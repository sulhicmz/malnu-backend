<?php

declare(strict_types=1);

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Library\LibraryPatron;
use App\Models\DigitalLibrary\Book;
use App\Models\DigitalLibrary\BookLoan;
use App\Models\Library\LibraryFine;
use App\Models\Library\LibraryHold;
use App\Services\LibraryManagementService;
use Carbon\Carbon;

class LibraryManagementTest extends TestCase
{
    private LibraryManagementService $libraryService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->libraryService = new LibraryManagementService();
    }

    public function testPatronCanBeCreated()
    {
        $data = [
            'user_id' => 'test-user-id',
            'library_card_number' => 'LC12345',
            'status' => 'active',
            'membership_start_date' => Carbon::now(),
            'max_loan_limit' => 5,
            'current_loans' => 0,
            'total_fines' => 0,
        ];

        $patron = $this->libraryService->createPatron($data);

        $this->assertNotNull($patron);
        $this->assertEquals('LC12345', $patron->library_card_number);
        $this->assertEquals('active', $patron->status);
    }

    public function testPatronCanBeRetrieved()
    {
        $patron = LibraryPatron::factory()->create();

        $retrievedPatron = $this->libraryService->getPatron($patron->id);

        $this->assertNotNull($retrievedPatron);
        $this->assertEquals($patron->id, $retrievedPatron->id);
    }

    public function testBookCanBeCheckedOut()
    {
        $patron = LibraryPatron::factory()->create([
            'current_loans' => 0,
            'max_loan_limit' => 5,
            'status' => 'active',
        ]);

        $book = Book::factory()->create([
            'available_quantity' => 5,
            'quantity' => 5,
        ]);

        $loan = $this->libraryService->checkoutBook($patron->id, $book->id);

        $this->assertNotNull($loan);
        $this->assertEquals('borrowed', $loan->status);
        $this->assertEquals($book->id, $loan->book_id);
    }

    public function testBookCannotBeCheckedOutWhenLimitReached()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Patron has reached maximum loan limit');

        $patron = LibraryPatron::factory()->create([
            'current_loans' => 5,
            'max_loan_limit' => 5,
        ]);

        $book = Book::factory()->create([
            'available_quantity' => 5,
        ]);

        $this->libraryService->checkoutBook($patron->id, $book->id);
    }

    public function testBookCanBeReturned()
    {
        $loan = BookLoan::factory()->create(['status' => 'borrowed']);

        $book = $loan->book;
        $book->available_quantity = $book->quantity - 1;
        $book->save();

        $returnedLoan = $this->libraryService->returnBook($loan->id);

        $this->assertEquals('returned', $returnedLoan->status);
        $this->assertNotNull($returnedLoan->return_date);
    }

    public function testBookCanBeRenewed()
    {
        $loan = BookLoan::factory()->create([
            'status' => 'borrowed',
            'due_date' => Carbon::now()->addDays(7),
        ]);

        $renewedLoan = $this->libraryService->renewBook($loan->id);

        $this->assertEquals($loan->due_date->addDays(14), $renewedLoan->due_date);
    }

    public function testHoldCanBePlaced()
    {
        $patron = LibraryPatron::factory()->create();
        $book = Book::factory()->create();

        $hold = $this->libraryService->placeHold($patron->id, $book->id);

        $this->assertNotNull($hold);
        $this->assertEquals('pending', $hold->status);
        $this->assertEquals('hold', $hold->hold_type);
    }

    public function testHoldCanBeCancelled()
    {
        $hold = LibraryHold::factory()->create(['status' => 'pending']);

        $result = $this->libraryService->cancelHold($hold->id);

        $this->assertTrue($result);
        $hold->refresh();
        $this->assertEquals('cancelled', $hold->status);
    }

    public function testFineCanBeCreated()
    {
        $patron = LibraryPatron::factory()->create();

        $fineData = [
            'patron_id' => $patron->id,
            'fine_type' => 'overdue',
            'amount' => 5.00,
            'description' => 'Test fine',
        ];

        $fine = $this->libraryService->createFine($fineData);

        $this->assertNotNull($fine);
        $this->assertEquals(5.00, $fine->amount);
    }

    public function testFineCanBePaid()
    {
        $fine = LibraryFine::factory()->create([
            'amount' => 10.00,
            'amount_paid' => 0,
            'payment_status' => 'pending',
        ]);

        $paidFine = $this->libraryService->payFine($fine->id, 10.00);

        $this->assertEquals(10.00, $paidFine->amount_paid);
        $this->assertEquals('paid', $paidFine->payment_status);
    }

    public function testReadingProgramCanBeCreated()
    {
        $programData = [
            'program_name' => 'Summer Reading Challenge',
            'program_type' => 'reading_challenge',
            'start_date' => Carbon::now(),
            'target_books' => 10,
        ];

        $program = $this->libraryService->createReadingProgram($programData);

        $this->assertNotNull($program);
        $this->assertEquals('Summer Reading Challenge', $program->program_name);
    }

    public function testPatronCanBeEnrolledInProgram()
    {
        $patron = LibraryPatron::factory()->create();
        $program = \App\Models\Library\LibraryReadingProgram::factory()->create();

        $participant = $this->libraryService->enrollInProgram($program->id, $patron->id);

        $this->assertNotNull($participant);
        $this->assertEquals('active', $participant->status);
    }

    public function testInventoryRecordCanBeCreated()
    {
        $book = Book::factory()->create();

        $inventoryData = [
            'book_id' => $book->id,
            'action_type' => 'stock_take',
            'expected_quantity' => 10,
            'actual_quantity' => 9,
            'performed_by' => 'Librarian',
        ];

        $inventory = $this->libraryService->createInventoryRecord($inventoryData);

        $this->assertNotNull($inventory);
        $this->assertEquals(-1, $inventory->difference);
    }

    public function testAcquisitionCanBeCreated()
    {
        $acquisitionData = [
            'acquisition_number' => 'ACQ001',
            'title' => 'New Book',
            'author' => 'Author Name',
            'quantity' => 5,
            'unit_cost' => 25.00,
        ];

        $acquisition = $this->libraryService->createAcquisition($acquisitionData);

        $this->assertNotNull($acquisition);
        $this->assertEquals(125.00, $acquisition->total_cost);
    }

    public function testSpaceCanBeBooked()
    {
        $space = \App\Models\Library\LibrarySpace::factory()->create();
        $user = \App\Models\User::factory()->create();

        $bookingData = [
            'space_id' => $space->id,
            'user_id' => $user->id,
            'start_time' => Carbon::now()->addDays(1)->setHour(9),
            'end_time' => Carbon::now()->addDays(1)->setHour(11),
            'status' => 'confirmed',
        ];

        $booking = $this->libraryService->bookSpace($bookingData);

        $this->assertNotNull($booking);
        $this->assertEquals('confirmed', $booking->status);
    }

    public function testMarcRecordCanBeCreated()
    {
        $book = Book::factory()->create();

        $marcData = [
            'book_id' => $book->id,
            'record_type' => 'language_material',
            'leader' => '00000nam  2200000i 4500',
        ];

        $record = $this->libraryService->createMarcRecord($marcData);

        $this->assertNotNull($record);
        $this->assertEquals('language_material', $record->record_type);
    }

    public function testPatronCanBorrowMoreWhenUnderLimit()
    {
        $patron = LibraryPatron::factory()->create([
            'current_loans' => 3,
            'max_loan_limit' => 5,
            'status' => 'active',
        ]);

        $this->assertTrue($patron->canBorrowMore());
    }

    public function testPatronCannotBorrowMoreWhenAtLimit()
    {
        $patron = LibraryPatron::factory()->create([
            'current_loans' => 5,
            'max_loan_limit' => 5,
            'status' => 'active',
        ]);

        $this->assertFalse($patron->canBorrowMore());
    }

    public function testPatronHasOutstandingFines()
    {
        $patron = LibraryPatron::factory()->create([
            'total_fines' => 25.00,
        ]);

        $this->assertTrue($patron->hasOutstandingFines());
    }
}
