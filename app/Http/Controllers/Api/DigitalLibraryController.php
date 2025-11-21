<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\ApiController;
use App\Models\DigitalLibrary\Book;
use Hypervel\Http\Request;
use Hypervel\Http\Response;

class DigitalLibraryController extends ApiController
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): array
    {
        try {
            $books = Book::with(['bookLoans', 'bookReviews'])->paginate(
                $request->get('per_page', 15)
            );

            return $this->success($books, 'Books retrieved successfully');
        } catch (\Exception $e) {
            return $this->error($e->getMessage(), 500);
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): array
    {
        try {
            // Basic validation
            $validated = $request->validate([
                'title' => 'required|string|max:255',
                'author' => 'required|string|max:255',
                'isbn' => 'nullable|string|unique:books,isbn',
                'publisher' => 'nullable|string|max:255',
                'publication_year' => 'nullable|integer|min:1000|max:' . (date('Y') + 1),
                'category' => 'nullable|string|max:100',
                'description' => 'nullable|string',
                'total_copies' => 'required|integer|min:0',
                'available_copies' => 'required|integer|min:0',
                'location' => 'nullable|string|max:100',
                'language' => 'nullable|string|max:50',
            ]);

            $book = Book::create($validated);

            return $this->success($book, 'Book created successfully', 201);
        } catch (\Exception $e) {
            return $this->error($e->getMessage(), 422, $e->getMessage());
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(int $id): array
    {
        try {
            $book = Book::with(['bookLoans', 'bookReviews'])->findOrFail($id);

            return $this->success($book, 'Book retrieved successfully');
        } catch (\Exception $e) {
            return $this->error('Book not found', 404);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, int $id): array
    {
        try {
            $book = Book::findOrFail($id);

            $validated = $request->validate([
                'title' => 'sometimes|required|string|max:255',
                'author' => 'sometimes|required|string|max:255',
                'isbn' => 'nullable|string|unique:books,isbn,' . $id,
                'publisher' => 'nullable|string|max:255',
                'publication_year' => 'nullable|integer|min:1000|max:' . (date('Y') + 1),
                'category' => 'nullable|string|max:100',
                'description' => 'nullable|string',
                'total_copies' => 'sometimes|required|integer|min:0',
                'available_copies' => 'sometimes|required|integer|min:0',
                'location' => 'nullable|string|max:100',
                'language' => 'nullable|string|max:50',
            ]);

            $book->update($validated);

            return $this->success($book, 'Book updated successfully');
        } catch (\Exception $e) {
            return $this->error($e->getMessage(), 422);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(int $id): array
    {
        try {
            $book = Book::findOrFail($id);
            $book->delete();

            return $this->success(null, 'Book deleted successfully');
        } catch (\Exception $e) {
            return $this->error('Book not found or could not be deleted', 404);
        }
    }
}