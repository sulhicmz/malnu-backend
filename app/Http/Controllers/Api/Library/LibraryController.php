<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Library;

use App\Http\Controllers\Controller;
use App\Services\LibraryService;
use Hyperf\Di\Annotation\Inject;

class LibraryController extends Controller
{
    #[Inject]
    private LibraryService $libraryService;

    /**
     * Search books with filters
     */
    public function search()
    {
        $filters = $this->request->all();
        
        $page = (int) $this->request->input('page', 1);
        $perPage = (int) $this->request->input('per_page', 20);

        $result = $this->libraryService->searchBooks($filters, $page, $perPage);
        
        return $this->successResponse($result);
    }

    /**
     * Get book details
     */
    public function getBook(string $id)
    {
        $book = $this->libraryService->getBookDetails($id);
        
        if (!$book) {
            return $this->notFoundResponse('Book not found');
        }

        return $this->successResponse($book);
    }

    /**
     * Checkout a book
     */
    public function checkout()
    {
        $bookId = $this->request->input('book_id');
        $userId = $this->request->getAttribute('user_id');
        $libraryCardId = $this->request->input('library_card_id');

        if (!$bookId || !$userId) {
            return $this->errorResponse('book_id and user_id are required', 422);
        }

        $result = $this->libraryService->checkoutBook($bookId, $userId, $libraryCardId);
        
        if (!$result['success']) {
            return $this->errorResponse($result['message'], 422);
        }

        return $this->successResponse($result['data'], 'Book checked out successfully');
    }

    /**
     * Return a book
     */
    public function returnBook(string $id)
    {
        $result = $this->libraryService->returnBook($id);
        
        if (!$result['success']) {
            return $this->errorResponse($result['message'], 422);
        }

        return $this->successResponse($result['data'], 'Book returned successfully');
    }

    /**
     * Renew a book loan
     */
    public function renew(string $id)
    {
        $result = $this->libraryService->renewBook($id);
        
        if (!$result['success']) {
            return $this->errorResponse($result['message'], 422);
        }

        return $this->successResponse($result['data'], 'Book renewed successfully');
    }

    /**
     * Place a book on hold
     */
    public function placeHold()
    {
        $bookId = $this->request->input('book_id');
        $userId = $this->request->getAttribute('user_id');

        if (!$bookId || !$userId) {
            return $this->errorResponse('book_id and user_id are required', 422);
        }

        $result = $this->libraryService->placeBookHold($bookId, $userId);
        
        if (!$result['success']) {
            return $this->errorResponse($result['message'], 422);
        }

        return $this->successResponse($result['data'], 'Hold placed successfully');
    }

    /**
     * Cancel a book hold
     */
    public function cancelHold(string $id)
    {
        $userId = $this->request->getAttribute('user_id');
        
        $result = $this->libraryService->cancelBookHold($id, $userId);
        
        if (!$result['success']) {
            return $this->errorResponse($result['message'], 422);
        }

        return $this->successResponse($result['data'], 'Hold cancelled successfully');
    }

    /**
     * Process book holds (when book becomes available)
     */
    public function processHolds(string $bookId)
    {
        $result = $this->libraryService->processBookHolds($bookId);
        
        if (!$result['success']) {
            return $this->errorResponse($result['message'], 422);
        }

        return $this->successResponse($result, 'Holds processed successfully');
    }

    /**
     * Create library card
     */
    public function createLibraryCard()
    {
        $userId = $this->request->getAttribute('user_id');
        $cardNumber = $this->request->input('card_number');

        if (!$userId || !$cardNumber) {
            return $this->errorResponse('user_id and card_number are required', 422);
        }

        $result = $this->libraryService->createLibraryCard($userId, $cardNumber);
        
        if (!$result['success']) {
            return $this->errorResponse($result['message'], 422);
        }

        return $this->successResponse($result['data'], 'Library card created successfully');
    }

    /**
     * Get reading history
     */
    public function getReadingHistory()
    {
        $userId = $this->request->getAttribute('user_id');
        
        $page = (int) $this->request->input('page', 1);
        $perPage = (int) $this->request->input('per_page', 20);

        $result = $this->libraryService->getReadingHistory($userId, $page, $perPage);
        
        return $this->successResponse($result);
    }

    /**
     * Get library statistics
     */
    public function getStatistics()
    {
        $statistics = $this->libraryService->getLibraryStatistics();
        
        return $this->successResponse($statistics);
    }
}
