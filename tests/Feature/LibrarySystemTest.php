<?php

declare(strict_types=1);

namespace Tests\Feature;

use HyperfTest\HttpTestCase;
use App\Models\DigitalLibrary\Book;
use App\Models\DigitalLibrary\BookLoan;
use App\Models\DigitalLibrary\BookHold;
use App\Models\DigitalLibrary\BookCategory;
use App\Models\DigitalLibrary\BookSubject;
use App\Models\DigitalLibrary\LibraryCard;
use App\Models\DigitalLibrary\LoanPolicy;
use App\Models\User;

class LibrarySystemTest extends HttpTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
    }

    /**
     * Test searching books
     */
    public function test_search_books()
    {
        $response = $this->get('/api/library/books/search');
        
        $this->assertEquals(200, $response->getStatusCode());
        $data = json_decode((string) $response->getBody(), true);
        $this->assertArrayHasKey('success', $data);
        $this->assertArrayHasKey('data', $data);
    }

    /**
     * Test searching books with filters
     */
    public function test_search_books_with_filters()
    {
        $response = $this->get('/api/library/books/search?search=test&category=fiction&page=1&per_page=10');
        
        $this->assertEquals(200, $response->getStatusCode());
    }

    /**
     * Test searching available books only
     */
    public function test_search_available_books()
    {
        $response = $this->get('/api/library/books/search?available_only=true');
        
        $this->assertEquals(200, $response->getStatusCode());
    }

    /**
     * Test getting book details
     */
    public function test_get_book_details()
    {
        $book = Book::first();
        
        if (!$book) {
            $this->markTestSkipped('No books in database');
            return;
        }

        $response = $this->get("/api/library/books/{$book->id}");
        
        $this->assertEquals(200, $response->getStatusCode());
        $data = json_decode((string) $response->getBody(), true);
        $this->assertArrayHasKey('data', $data);
        $this->assertEquals($book->id, $data['data']['id']);
    }

    /**
     * Test getting non-existent book
     */
    public function test_get_nonexistent_book()
    {
        $response = $this->get('/api/library/books/00000000-0000-0000-000000000001');
        
        $this->assertEquals(404, $response->getStatusCode());
        $data = json_decode((string) $response->getBody(), true);
        $this->assertArrayHasKey('success', $data);
        $this->assertFalse($data['success']);
    }

    /**
     * Test library statistics
     */
    public function test_get_library_statistics()
    {
        $response = $this->get('/api/library/statistics');
        
        $this->assertEquals(200, $response->getStatusCode());
        $data = json_decode((string) $response->getBody(), true);
        $this->assertArrayHasKey('data', $data);
        $this->assertArrayHasKey('books', $data['data']);
        $this->assertArrayHasKey('loans', $data['data']);
        $this->assertArrayHasKey('holds', $data['data']);
        $this->assertArrayHasKey('library_cards', $data['data']);
    }

    /**
     * Test book categories model
     */
    public function test_book_categories_relationships()
    {
        $category = BookCategory::create([
            'name' => 'Fiction',
            'code' => 'fiction',
            'description' => 'Fiction books',
        ]);

        $this->assertDatabaseHas('book_categories', [
            'id' => $category->id,
            'name' => 'Fiction',
            'code' => 'fiction',
        ]);

        $this->assertInstanceOf(BookCategory::class, $category);
    }

    /**
     * Test book subjects model
     */
    public function test_book_subjects_model()
    {
        $subject = BookSubject::create([
            'name' => 'Mathematics',
            'code' => 'math',
            'description' => 'Mathematical books',
        ]);

        $this->assertDatabaseHas('book_subjects', [
            'id' => $subject->id,
            'name' => 'Mathematics',
            'code' => 'math',
        ]);

        $this->assertInstanceOf(BookSubject::class, $subject);
    }

    /**
     * Test loan policy model
     */
    public function test_loan_policy_model()
    {
        $policy = LoanPolicy::create([
            'name' => 'Student Policy',
            'user_type' => 'student',
            'max_books' => 5,
            'loan_duration_days' => 14,
            'renewal_limit' => 2,
            'fine_per_day' => 0.50,
            'grace_period_days' => 3,
        ]);

        $this->assertDatabaseHas('loan_policies', [
            'id' => $policy->id,
            'name' => 'Student Policy',
            'user_type' => 'student',
        ]);

        $this->assertInstanceOf(LoanPolicy::class, $policy);
        
        // Test fine calculation
        $this->assertEquals(2.00, $policy->calculateFine(7)); // 7 days - 3 grace = 4 days * 0.50
    }

    /**
     * Test book hold model
     */
    public function test_book_hold_model()
    {
        $user = User::first();
        $book = Book::first();

        if (!$user || !$book) {
            $this->markTestSkipped('No user or book in database');
            return;
        }

        $hold = BookHold::create([
            'book_id' => $book->id,
            'patron_id' => $user->id,
            'hold_date' => now(),
            'status' => 'pending',
        ]);

        $this->assertDatabaseHas('book_holds', [
            'id' => $hold->id,
            'book_id' => $book->id,
            'patron_id' => $user->id,
        ]);

        $this->assertInstanceOf(BookHold::class, $hold);
    }

    /**
     * Test library card model
     */
    public function test_library_card_model()
    {
        $user = User::first();

        if (!$user) {
            $this->markTestSkipped('No user in database');
            return;
        }

        $card = LibraryCard::create([
            'user_id' => $user->id,
            'card_number' => 'LIB-' . time(),
            'issue_date' => now(),
            'status' => 'active',
        ]);

        $this->assertDatabaseHas('library_cards', [
            'id' => $card->id,
            'user_id' => $user->id,
            'status' => 'active',
        ]);

        $this->assertInstanceOf(LibraryCard::class, $card);
    }

    /**
     * Test book loan scopes
     */
    public function test_book_loan_scopes()
    {
        $user = User::first();
        $book = Book::first();

        if (!$user || !$book) {
            $this->markTestSkipped('No user or book in database');
            return;
        }

        $activeLoan = BookLoan::where('borrower_id', $user->id)
            ->whereNull('return_date')
            ->where('status', 'borrowed')
            ->first();

        $this->assertInstanceOf(BookLoan::class, $activeLoan);
    }

    /**
     * Test library card scopes
     */
    public function test_library_card_scopes()
    {
        $card = LibraryCard::active()->first();

        if (!$card) {
            $this->markTestSkipped('No active library card in database');
            return;
        }

        $this->assertInstanceOf(LibraryCard::class, $card);
        $this->assertTrue($card->isActive());
    }

    /**
     * Test loan policy scopes
     */
    public function test_loan_policy_scopes()
    {
        $policy = LoanPolicy::active()->byUserType('student')->first();

        if (!$policy) {
            $this->markTestSkipped('No active loan policy for students');
            return;
        }

        $this->assertInstanceOf(LoanPolicy::class, $policy);
        $this->assertTrue($policy->is_active);
    }

    /**
     * Test book hold scopes
     */
    public function test_book_hold_scopes()
    {
        $book = Book::first();

        if (!$book) {
            $this->markTestSkipped('No book in database');
            return;
        }

        $pendingHolds = BookHold::where('book_id', $book->id)
            ->pending()
            ->get();

        $this->assertIsArray($pendingHolds);
        $this->assertCount(0, $pendingHolds); // Assuming no holds exist
    }
}
