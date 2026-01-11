<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\DigitalLibrary\Book;
use App\Models\DigitalLibrary\BookLoan;
use App\Models\Library\LibraryPatron;
use App\Models\Library\LibraryFine;
use App\Models\Library\LibraryHold;
use App\Models\Library\LibraryInventory;
use App\Models\Library\LibraryAcquisition;
use App\Models\Library\LibraryReadingProgram;
use App\Models\Library\LibraryReadingProgramParticipant;
use App\Models\Library\LibraryAnalytics;
use App\Models\Library\LibrarySpace;
use App\Models\Library\LibrarySpaceBooking;
use App\Models\Library\MarcRecord;
use App\Models\Library\MarcField;
use App\Models\User;
use Exception;
use Carbon\Carbon;

class LibraryManagementService
{
    public function createPatron(array $data): LibraryPatron
    {
        return LibraryPatron::create($data);
    }

    public function getPatron(string $id): ?LibraryPatron
    {
        return LibraryPatron::find($id);
    }

    public function getPatronByUser(string $userId): ?LibraryPatron
    {
        return LibraryPatron::where('user_id', $userId)->first();
    }

    public function updatePatron(string $id, array $data): bool
    {
        $patron = LibraryPatron::find($id);
        if (!$patron) {
            return false;
        }
        return $patron->update($data);
    }

    public function deletePatron(string $id): bool
    {
        $patron = LibraryPatron::find($id);
        if (!$patron) {
            return false;
        }
        return $patron->delete();
    }

    public function getAllPatrons(array $filters = [])
    {
        $query = LibraryPatron::query();
        
        if (isset($filters['status'])) {
            $query->where('status', $filters['status']);
        }
        
        if (isset($filters['search'])) {
            $search = $filters['search'];
            $query->whereHas('user', function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }
        
        return $query->paginate($filters['per_page'] ?? 20);
    }

    public function checkoutBook(string $patronId, string $bookId, int $loanDays = 14): BookLoan
    {
        $patron = LibraryPatron::findOrFail($patronId);
        $book = Book::findOrFail($bookId);

        if (!$patron->canBorrowMore()) {
            throw new Exception('Patron has reached maximum loan limit');
        }

        if ($book->available_quantity <= 0) {
            throw new Exception('Book is not available for checkout');
        }

        $loan = BookLoan::create([
            'book_id' => $bookId,
            'borrower_id' => $patron->user_id,
            'loan_date' => Carbon::now(),
            'due_date' => Carbon::now()->addDays($loanDays),
            'status' => 'borrowed',
        ]);

        $book->decrement('available_quantity');
        $patron->increment('current_loans');

        return $loan;
    }

    public function returnBook(string $loanId): BookLoan
    {
        $loan = BookLoan::findOrFail($loanId);

        if ($loan->status === 'returned') {
            throw new Exception('Book has already been returned');
        }

        $loan->update([
            'return_date' => Carbon::now(),
            'status' => 'returned',
        ]);

        $loan->book->increment('available_quantity');
        $loan->borrower->patron->decrement('current_loans');

        $this->calculateOverdueFine($loan);

        return $loan;
    }

    public function renewBook(string $loanId, int $additionalDays = 14): BookLoan
    {
        $loan = BookLoan::findOrFail($loanId);

        if ($loan->status === 'returned') {
            throw new Exception('Cannot renew a returned book');
        }

        if ($loan->patron->hasOutstandingFines()) {
            throw new Exception('Cannot renew with outstanding fines');
        }

        $loan->update([
            'due_date' => $loan->due_date->addDays($additionalDays),
        ]);

        return $loan;
    }

    public function placeHold(string $patronId, string $bookId, string $holdType = 'hold'): LibraryHold
    {
        return LibraryHold::create([
            'book_id' => $bookId,
            'patron_id' => $patronId,
            'hold_type' => $holdType,
            'status' => 'pending',
            'request_date' => Carbon::now(),
        ]);
    }

    public function cancelHold(string $holdId): bool
    {
        $hold = LibraryHold::findOrFail($holdId);
        if ($hold->status === 'fulfilled') {
            throw new Exception('Cannot cancel a fulfilled hold');
        }
        return $hold->update(['status' => 'cancelled']);
    }

    public function fulfillHold(string $holdId): LibraryHold
    {
        $hold = LibraryHold::findOrFail($holdId);
        $hold->update([
            'status' => 'fulfilled',
            'ready_date' => Carbon::now(),
            'fulfilled_date' => Carbon::now(),
        ]);
        return $hold;
    }

    protected function calculateOverdueFine(BookLoan $loan): void
    {
        if (Carbon::now()->lte($loan->due_date)) {
            return;
        }

        $daysOverdue = Carbon::now()->diffInDays($loan->due_date);
        $finePerDay = 0.50;
        $totalFine = $daysOverdue * $finePerDay;

        LibraryFine::create([
            'patron_id' => $loan->borrower->patron->id,
            'loan_id' => $loan->id,
            'fine_type' => 'overdue',
            'amount' => $totalFine,
            'amount_paid' => 0,
            'payment_status' => 'pending',
            'fine_date' => Carbon::now(),
            'description' => "Overdue fine for book: {$loan->book->title}",
        ]);

        $loan->borrower->patron->increment('total_fines', $totalFine);
    }

    public function createFine(array $data): LibraryFine
    {
        $fine = LibraryFine::create($data);
        $fine->patron->increment('total_fines', $data['amount']);
        return $fine;
    }

    public function payFine(string $fineId, float $amount): LibraryFine
    {
        $fine = LibraryFine::findOrFail($fineId);
        $newAmountPaid = min($fine->amount_paid + $amount, $fine->amount);

        $fine->update(['amount_paid' => $newAmountPaid]);

        if ($newAmountPaid >= $fine->amount) {
            $fine->update(['payment_status' => 'paid', 'payment_date' => Carbon::now()]);
            $fine->patron->decrement('total_fines', $fine->getRemainingBalanceAttribute());
        }

        return $fine;
    }

    public function waiveFine(string $fineId): LibraryFine
    {
        $fine = LibraryFine::findOrFail($fineId);
        $fine->update([
            'payment_status' => 'waived',
            'amount_paid' => $fine->amount,
        ]);
        $fine->patron->decrement('total_fines', $fine->getRemainingBalanceAttribute());
        return $fine;
    }

    public function createInventoryRecord(array $data): LibraryInventory
    {
        $difference = $data['actual_quantity'] - $data['expected_quantity'];
        $data['difference'] = $difference;
        $data['inventory_date'] = Carbon::now();
        
        return LibraryInventory::create($data);
    }

    public function createAcquisition(array $data): LibraryAcquisition
    {
        $data['total_cost'] = $data['unit_cost'] * $data['quantity'];
        $data['order_date'] = Carbon::now();
        return LibraryAcquisition::create($data);
    }

    public function markAcquisitionReceived(string $id, array $details = []): LibraryAcquisition
    {
        $acquisition = LibraryAcquisition::findOrFail($id);
        $acquisition->update([
            'status' => 'received',
            'received_date' => Carbon::now(),
            ...$details,
        ]);
        
        Book::create([
            'isbn' => $acquisition->isbn,
            'title' => $acquisition->title,
            'author' => $acquisition->author,
            'publisher' => $acquisition->publisher,
            'quantity' => $acquisition->quantity,
            'available_quantity' => $acquisition->quantity,
        ]);
        
        return $acquisition;
    }

    public function createReadingProgram(array $data): LibraryReadingProgram
    {
        return LibraryReadingProgram::create($data);
    }

    public function enrollInProgram(string $programId, string $patronId): LibraryReadingProgramParticipant
    {
        return LibraryReadingProgramParticipant::create([
            'program_id' => $programId,
            'patron_id' => $patronId,
            'enrollment_date' => Carbon::now(),
            'status' => 'active',
        ]);
    }

    public function recordBooksRead(string $participantId, int $count = 1): LibraryReadingProgramParticipant
    {
        $participant = LibraryReadingProgramParticipant::findOrFail($participantId);
        $participant->increment('books_read', $count);
        
        $program = $participant->program;
        if ($program->target_books && $participant->books_read >= $program->target_books) {
            $participant->complete();
        }
        
        return $participant;
    }

    public function createSpace(array $data): LibrarySpace
    {
        return LibrarySpace::create($data);
    }

    public function bookSpace(array $data): LibrarySpaceBooking
    {
        $space = LibrarySpace::findOrFail($data['space_id']);
        
        $this->validateSpaceAvailability($space, $data['start_time'], $data['end_time']);
        
        return LibrarySpaceBooking::create($data);
    }

    protected function validateSpaceAvailability(LibrarySpace $space, string $startTime, string $endTime): void
    {
        $conflict = LibrarySpaceBooking::where('space_id', $space->id)
            ->where('status', 'confirmed')
            ->where(function ($query) use ($startTime, $endTime) {
                $query->whereBetween('start_time', [$startTime, $endTime])
                    ->orWhereBetween('end_time', [$startTime, $endTime])
                    ->orWhere(function ($q) use ($startTime, $endTime) {
                        $q->where('start_time', '<=', $startTime)
                          ->where('end_time', '>=', $endTime);
                    });
            })
            ->exists();

        if ($conflict) {
            throw new Exception('Space is already booked for the requested time slot');
        }
    }

    public function cancelSpaceBooking(string $bookingId): LibrarySpaceBooking
    {
        $booking = LibrarySpaceBooking::findOrFail($bookingId);
        $booking->update(['status' => 'cancelled']);
        return $booking;
    }

    public function createMarcRecord(array $data): MarcRecord
    {
        return MarcRecord::create($data);
    }

    public function addMarcField(string $recordId, array $fieldData): MarcField
    {
        $fieldData['marc_record_id'] = $recordId;
        return MarcField::create($fieldData);
    }

    public function recordAnalytics(string $date = null): LibraryAnalytics
    {
        $date = $date ?? Carbon::now()->toDateString();

        $checkouts = BookLoan::whereDate('loan_date', $date)->count();
        $returns = BookLoan::whereDate('return_date', $date)->count();
        $holds = LibraryHold::whereDate('request_date', $date)->count();

        return LibraryAnalytics::create([
            'analytics_date' => $date,
            'checkouts' => $checkouts,
            'returns' => $returns,
            'renewals' => 0,
            'holds_placed' => $holds,
            'page_views' => 0,
            'unique_patrons' => 0,
        ]);
    }

    public function getPopularBooks(int $limit = 10, int $days = 30)
    {
        return BookLoan::select('book_id')
            ->selectRaw('COUNT(*) as checkout_count')
            ->where('loan_date', '>=', Carbon::now()->subDays($days))
            ->groupBy('book_id')
            ->orderByDesc('checkout_count')
            ->limit($limit)
            ->with('book')
            ->get();
    }

    public function getPatronReadingHistory(string $patronId, array $filters = [])
    {
        $patron = LibraryPatron::findOrFail($patronId);
        
        $query = $patron->user->loans()->with('book');
        
        if (isset($filters['status'])) {
            $query->where('status', $filters['status']);
        }
        
        if (isset($filters['from_date'])) {
            $query->where('loan_date', '>=', $filters['from_date']);
        }
        
        if (isset($filters['to_date'])) {
            $query->where('loan_date', '<=', $filters['to_date']);
        }
        
        return $query->orderByDesc('loan_date')
            ->paginate($filters['per_page'] ?? 20);
    }

    public function generateOverdueFines(): int
    {
        $overdueLoans = BookLoan::where('status', 'borrowed')
            ->where('due_date', '<', Carbon::now())
            ->whereDoesntHave('fines')
            ->get();

        $count = 0;
        foreach ($overdueLoans as $loan) {
            $this->calculateOverdueFine($loan);
            $count++;
        }

        return $count;
    }
}
