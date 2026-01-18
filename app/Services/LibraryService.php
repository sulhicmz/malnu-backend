<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\DigitalLibrary\Book;
use App\Models\DigitalLibrary\BookLoan;
use App\Models\DigitalLibrary\BookHold;
use App\Models\DigitalLibrary\BookCategory;
use App\Models\DigitalLibrary\BookSubject;
use App\Models\DigitalLibrary\BookAuthor;
use App\Models\DigitalLibrary\LibraryCard;
use App\Models\DigitalLibrary\ReadingHistory;
use App\Models\DigitalLibrary\LoanPolicy;
use App\Models\User;
use Hyperf\DbConnection\Db;
use Hyperf\Di\Annotation\Inject;
use Psr\Log\LoggerInterface;

class LibraryService
{
    #[Inject]
    private LoggerInterface $logger;

    /**
     * Search books with filters
     */
    public function searchBooks(array $filters = [], int $page = 1, int $perPage = 20): array
    {
        $query = Book::query();

        if (!empty($filters['search'])) {
            $query = $query->scopeSearch($filters['search']);
        }

        if (!empty($filters['category'])) {
            $query = $query->scopeByCategory($filters['category']);
        }

        if (!empty($filters['subject'])) {
            $query = $query->scopeBySubject($filters['subject']);
        }

        if (!empty($filters['available_only'])) {
            $query = $query->scopeAvailable();
        }

        $total = $query->count();
        $books = $query->offset(($page - 1) * $perPage)
            ->limit($perPage)
            ->get();

        return [
            'data' => $books,
            'pagination' => [
                'total' => $total,
                'per_page' => $perPage,
                'current_page' => $page,
                'last_page' => (int) ceil($total / $perPage),
            ],
        ];
    }

    /**
     * Get book details with relationships
     */
    public function getBookDetails(string $bookId): ?array
    {
        $book = Book::with(['bookAuthors', 'categories', 'subjects', 'bookReviews'])
            ->find($bookId);

        if (!$book) {
            return null;
        }

        return $book->toArray();
    }

    /**
     * Checkout a book to a patron
     */
    public function checkoutBook(string $bookId, string $userId, ?string $libraryCardId = null): array
    {
        $book = Book::find($bookId);
        
        if (!$book) {
            return ['success' => false, 'message' => 'Book not found'];
        }

        if ($book->available_quantity <= 0) {
            return ['success' => false, 'message' => 'Book is not available'];
        }

        $policy = $this->getLoanPolicyForUser($userId);
        if (!$policy) {
            return ['success' => false, 'message' => 'No loan policy found for user type'];
        }

        $activeLoans = BookLoan::where('borrower_id', $userId)
            ->scopeActive()
            ->count();

        if ($activeLoans >= $policy->max_books) {
            return [
                'success' => false, 
                'message' => "Maximum loan limit ({$policy->max_books}) reached"
            ];
        }

        $dueDate = now()->addDays($policy->loan_duration_days);

        $loan = BookLoan::create([
            'id' => Db::raw('(UUID())'),
            'book_id' => $bookId,
            'borrower_id' => $userId,
            'loan_date' => now(),
            'due_date' => $dueDate,
            'due_date_original' => $dueDate,
            'status' => 'borrowed',
            'library_card_id' => $libraryCardId,
        ]);

        $book->decrement('available_quantity');

        $this->logger->info("Book checked out", [
            'book_id' => $bookId,
            'user_id' => $userId,
            'loan_id' => $loan->id,
        ]);

        return ['success' => true, 'data' => $loan->toArray()];
    }

    /**
     * Return a book
     */
    public function returnBook(string $loanId): array
    {
        $loan = BookLoan::find($loanId);
        
        if (!$loan) {
            return ['success' => false, 'message' => 'Loan not found'];
        }

        if ($loan->return_date) {
            return ['success' => false, 'message' => 'Book already returned'];
        }

        $loan->return_date = now();
        $loan->status = 'returned';

        if ($loan->isOverdue()) {
            $policy = $this->getLoanPolicyForUser($loan->borrower_id);
            if ($policy) {
                $overdueDays = now()->diffInDays($loan->due_date);
                $loan->fine_amount = $policy->calculateFine($overdueDays);
            }
        }

        $loan->save();

        $book = $loan->book;
        $book->increment('available_quantity');

        ReadingHistory::create([
            'id' => Db::raw('(UUID())'),
            'book_id' => $loan->book_id,
            'user_id' => $loan->borrower_id,
            'loan_date' => $loan->loan_date,
            'return_date' => $loan->return_date,
        ]);

        $this->logger->info("Book returned", [
            'loan_id' => $loanId,
            'user_id' => $loan->borrower_id,
            'fine_amount' => $loan->fine_amount,
        ]);

        return ['success' => true, 'data' => $loan->toArray()];
    }

    /**
     * Renew a book loan
     */
    public function renewBook(string $loanId): array
    {
        $loan = BookLoan::find($loanId);
        
        if (!$loan) {
            return ['success' => false, 'message' => 'Loan not found'];
        }

        if ($loan->return_date) {
            return ['success' => false, 'message' => 'Book already returned'];
        }

        if ($loan->isOverdue()) {
            return ['success' => false, 'message' => 'Cannot renew overdue book'];
        }

        $policy = $this->getLoanPolicyForUser($loan->borrower_id);
        if (!$policy || !$policy->isRenewalAllowed($loan->renewal_count)) {
            return [
                'success' => false, 
                'message' => "Renewal limit ({$policy->renewal_limit}) reached"
            ];
        }

        $oldDueDate = $loan->due_date;
        $newDueDate = $oldDueDate->addDays($policy->loan_duration_days);

        $loan->due_date = $newDueDate;
        $loan->renewal_count += 1;
        $loan->save();

        $this->logger->info("Book renewed", [
            'loan_id' => $loanId,
            'user_id' => $loan->borrower_id,
            'renewal_count' => $loan->renewal_count,
        ]);

        return ['success' => true, 'data' => $loan->toArray()];
    }

    /**
     * Place a book on hold
     */
    public function placeBookHold(string $bookId, string $userId): array
    {
        $book = Book::find($bookId);
        
        if (!$book) {
            return ['success' => false, 'message' => 'Book not found'];
        }

        $existingHold = BookHold::where('book_id', $bookId)
            ->where('patron_id', $userId)
            ->scopePending()
            ->first();

        if ($existingHold) {
            return ['success' => false, 'message' => 'Hold already exists'];
        }

        $hold = BookHold::create([
            'id' => Db::raw('(UUID())'),
            'book_id' => $bookId,
            'patron_id' => $userId,
            'hold_date' => now(),
            'status' => 'pending',
        ]);

        $this->logger->info("Book hold placed", [
            'book_id' => $bookId,
            'user_id' => $userId,
            'hold_id' => $hold->id,
        ]);

        return ['success' => true, 'data' => $hold->toArray()];
    }

    /**
     * Cancel a book hold
     */
    public function cancelBookHold(string $holdId, string $userId): array
    {
        $hold = BookHold::find($holdId);
        
        if (!$hold) {
            return ['success' => false, 'message' => 'Hold not found'];
        }

        if ($hold->patron_id !== $userId) {
            return ['success' => false, 'message' => 'Unauthorized'];
        }

        if ($hold->is_cancelled) {
            return ['success' => false, 'message' => 'Hold already cancelled'];
        }

        if ($hold->is_ready) {
            return ['success' => false, 'message' => 'Hold is ready for pickup'];
        }

        $hold->is_cancelled = true;
        $hold->status = 'cancelled';
        $hold->save();

        return ['success' => true, 'data' => $hold->toArray()];
    }

    /**
     * Process book holds (when book becomes available)
     */
    public function processBookHolds(string $bookId): array
    {
        $holds = BookHold::where('book_id', $bookId)
            ->scopePending()
            ->orderBy('hold_date')
            ->get();

        $processedCount = 0;
        foreach ($holds as $hold) {
            $hold->is_ready = true;
            $hold->status = 'ready';
            $hold->save();
            $processedCount++;
        }

        return [
            'success' => true,
            'processed_count' => $processedCount,
            'message' => "Processed {$processedCount} holds",
        ];
    }

    /**
     * Create library card for user
     */
    public function createLibraryCard(string $userId, string $cardNumber): array
    {
        $existingCard = LibraryCard::where('card_number', $cardNumber)->first();
        if ($existingCard) {
            return ['success' => false, 'message' => 'Card number already exists'];
        }

        $card = LibraryCard::create([
            'id' => Db::raw('(UUID())'),
            'user_id' => $userId,
            'card_number' => $cardNumber,
            'issue_date' => now(),
            'status' => 'active',
        ]);

        return ['success' => true, 'data' => $card->toArray()];
    }

    /**
     * Get user's reading history
     */
    public function getReadingHistory(string $userId, int $page = 1, int $perPage = 20): array
    {
        $query = ReadingHistory::where('user_id', $userId)
            ->orderBy('return_date', 'desc');

        $total = $query->count();
        $history = $query->offset(($page - 1) * $perPage)
            ->limit($perPage)
            ->with('book')
            ->get();

        return [
            'data' => $history,
            'pagination' => [
                'total' => $total,
                'per_page' => $perPage,
                'current_page' => $page,
                'last_page' => (int) ceil($total / $perPage),
            ],
        ];
    }

    /**
     * Get loan policy for user type
     */
    protected function getLoanPolicyForUser(string $userId): ?LoanPolicy
    {
        $user = User::find($userId);
        if (!$user) {
            return null;
        }

        $userType = $this->determineUserType($user);

        return LoanPolicy::scopeByUserType($userType)
            ->scopeActive()
            ->first();
    }

    /**
     * Determine user type for loan policy
     */
    protected function determineUserType(User $user): string
    {
        if ($user->role === 'student') {
            return 'student';
        } elseif ($user->role === 'teacher' || $user->role === 'staff') {
            return 'staff';
        }

        return 'general';
    }

    /**
     * Get library statistics
     */
    public function getLibraryStatistics(): array
    {
        $totalBooks = Book::count();
        $availableBooks = Book::scopeAvailable()->count();
        $totalLoans = BookLoan::count();
        $activeLoans = BookLoan::scopeActive()->count();
        $overdueLoans = BookLoan::scopeOverdue()->count();
        $totalHolds = BookHold::count();
        $activeHolds = BookHold::scopeActive()->count();
        $activeLibraryCards = LibraryCard::scopeActive()->count();

        return [
            'books' => [
                'total' => $totalBooks,
                'available' => $availableBooks,
                'checked_out' => $totalBooks - $availableBooks,
            ],
            'loans' => [
                'total' => $totalLoans,
                'active' => $activeLoans,
                'overdue' => $overdueLoans,
            ],
            'holds' => [
                'total' => $totalHolds,
                'active' => $activeHolds,
            ],
            'library_cards' => [
                'active' => $activeLibraryCards,
            ],
        ];
    }
}
