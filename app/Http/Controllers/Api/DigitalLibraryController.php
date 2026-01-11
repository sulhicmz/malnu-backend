<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\BaseController;
use App\Models\DigitalLibrary\Book;
use App\Models\DigitalLibrary\BookLoan;
use App\Models\DigitalLibrary\BookReview;
use App\Models\DigitalLibrary\EbookFormat;
use Hyperf\HttpServer\Contract\RequestInterface;
use Hyperf\HttpServer\Contract\ResponseInterface;
use Psr\Container\ContainerInterface;

class DigitalLibraryController extends BaseController
{
    public function __construct(
        RequestInterface $request,
        ResponseInterface $response,
        ContainerInterface $container
    ) {
        parent::__construct($request, $response, $container);
    }

    public function indexBooks()
    {
        try {
            $query = Book::query();

            $search = $this->request->query('search');
            $format = $this->request->query('format');
            $page = (int) $this->request->query('page', 1);
            $limit = (int) $this->request->query('limit', 15);

            if ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('title', 'like', "%{$search}%")
                      ->orWhere('author', 'like', "%{$search}%")
                      ->orWhere('isbn', 'like', "%{$search}%");
                });
            }

            if ($format) {
                $query->where('format', $format);
            }

            $books = $query->orderBy('title', 'asc')->paginate($limit, ['*'], 'page', $page);

            return $this->successResponse($books, 'Books retrieved successfully');
        } catch (\Exception $e) {
            return $this->serverErrorResponse($e->getMessage());
        }
    }

    public function storeBook()
    {
        try {
            $data = $this->request->all();

            $requiredFields = ['title', 'author', 'isbn', 'publication_year'];
            $errors = [];
            
            foreach ($requiredFields as $field) {
                if (empty($data[$field])) {
                    $errors[$field] = ["The {$field} field is required."];
                }
            }

            if (!empty($errors)) {
                return $this->validationErrorResponse($errors);
            }

            $book = Book::create($data);

            return $this->successResponse($book, 'Book created successfully', 201);
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 'BOOK_CREATION_ERROR', null, 400);
        }
    }

    public function showBook(string $id)
    {
        try {
            $book = Book::with(['reviews'])->find($id);

            if (!$book) {
                return $this->notFoundResponse('Book not found');
            }

            return $this->successResponse($book, 'Book retrieved successfully');
        } catch (\Exception $e) {
            return $this->serverErrorResponse($e->getMessage());
        }
    }

    public function updateBook(string $id)
    {
        try {
            $book = Book::find($id);

            if (!$book) {
                return $this->notFoundResponse('Book not found');
            }

            $data = $this->request->all();
            $book->update($data);

            return $this->successResponse($book, 'Book updated successfully');
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 'BOOK_UPDATE_ERROR', null, 400);
        }
    }

    public function destroyBook(string $id)
    {
        try {
            $book = Book::find($id);

            if (!$book) {
                return $this->notFoundResponse('Book not found');
            }

            $book->delete();

            return $this->successResponse(null, 'Book deleted successfully');
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 'BOOK_DELETION_ERROR', null, 400);
        }
    }

    public function indexBookLoans()
    {
        try {
            $query = BookLoan::with(['book', 'student']);

            $studentId = $this->request->query('student_id');
            $status = $this->request->query('status');
            $page = (int) $this->request->query('page', 1);
            $limit = (int) $this->request->query('limit', 15);

            if ($studentId) {
                $query->where('student_id', $studentId);
            }

            if ($status) {
                $query->where('status', $status);
            }

            $loans = $query->orderBy('loan_date', 'desc')->paginate($limit, ['*'], 'page', $page);

            return $this->successResponse($loans, 'Book loans retrieved successfully');
        } catch (\Exception $e) {
            return $this->serverErrorResponse($e->getMessage());
        }
    }

    public function storeBookLoan()
    {
        try {
            $data = $this->request->all();

            $requiredFields = ['book_id', 'student_id'];
            $errors = [];
            
            foreach ($requiredFields as $field) {
                if (empty($data[$field])) {
                    $errors[$field] = ["The {$field} field is required."];
                }
            }

            if (!empty($errors)) {
                return $this->validationErrorResponse($errors);
            }

            $loan = BookLoan::create($data);

            return $this->successResponse($loan, 'Book loan created successfully', 201);
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 'BOOK_LOAN_CREATION_ERROR', null, 400);
        }
    }

    public function showBookLoan(string $id)
    {
        try {
            $loan = BookLoan::with(['book', 'student'])->find($id);

            if (!$loan) {
                return $this->notFoundResponse('Book loan not found');
            }

            return $this->successResponse($loan, 'Book loan retrieved successfully');
        } catch (\Exception $e) {
            return $this->serverErrorResponse($e->getMessage());
        }
    }

    public function updateBookLoan(string $id)
    {
        try {
            $loan = BookLoan::find($id);

            if (!$loan) {
                return $this->notFoundResponse('Book loan not found');
            }

            $data = $this->request->all();
            $loan->update($data);

            return $this->successResponse($loan, 'Book loan updated successfully');
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 'BOOK_LOAN_UPDATE_ERROR', null, 400);
        }
    }

    public function destroyBookLoan(string $id)
    {
        try {
            $loan = BookLoan::find($id);

            if (!$loan) {
                return $this->notFoundResponse('Book loan not found');
            }

            $loan->delete();

            return $this->successResponse(null, 'Book loan deleted successfully');
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 'BOOK_LOAN_DELETION_ERROR', null, 400);
        }
    }

    public function indexBookReviews()
    {
        try {
            $query = BookReview::with(['book', 'student']);

            $bookId = $this->request->query('book_id');
            $rating = $this->request->query('rating');
            $page = (int) $this->request->query('page', 1);
            $limit = (int) $this->request->query('limit', 15);

            if ($bookId) {
                $query->where('book_id', $bookId);
            }

            if ($rating) {
                $query->where('rating', '>=', $rating);
            }

            $reviews = $query->orderBy('created_at', 'desc')->paginate($limit, ['*'], 'page', $page);

            return $this->successResponse($reviews, 'Book reviews retrieved successfully');
        } catch (\Exception $e) {
            return $this->serverErrorResponse($e->getMessage());
        }
    }

    public function storeBookReview()
    {
        try {
            $data = $this->request->all();

            $requiredFields = ['book_id', 'student_id', 'rating', 'review'];
            $errors = [];
            
            foreach ($requiredFields as $field) {
                if (empty($data[$field])) {
                    $errors[$field] = ["The {$field} field is required."];
                }
            }

            if (!empty($errors)) {
                return $this->validationErrorResponse($errors);
            }

            $review = BookReview::create($data);

            return $this->successResponse($review, 'Book review created successfully', 201);
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 'BOOK_REVIEW_CREATION_ERROR', null, 400);
        }
    }

    public function showBookReview(string $id)
    {
        try {
            $review = BookReview::with(['book', 'student'])->find($id);

            if (!$review) {
                return $this->notFoundResponse('Book review not found');
            }

            return $this->successResponse($review, 'Book review retrieved successfully');
        } catch (\Exception $e) {
            return $this->serverErrorResponse($e->getMessage());
        }
    }

    public function updateBookReview(string $id)
    {
        try {
            $review = BookReview::find($id);

            if (!$review) {
                return $this->notFoundResponse('Book review not found');
            }

            $data = $this->request->all();
            $review->update($data);

            return $this->successResponse($review, 'Book review updated successfully');
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 'BOOK_REVIEW_UPDATE_ERROR', null, 400);
        }
    }

    public function destroyBookReview(string $id)
    {
        try {
            $review = BookReview::find($id);

            if (!$review) {
                return $this->notFoundResponse('Book review not found');
            }

            $review->delete();

            return $this->successResponse(null, 'Book review deleted successfully');
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 'BOOK_REVIEW_DELETION_ERROR', null, 400);
        }
    }

    public function indexEbookFormats()
    {
        try {
            $formats = EbookFormat::orderBy('name', 'asc')->paginate(15, ['*'], 'page', $this->request->query('page', 1));

            return $this->successResponse($formats, 'Ebook formats retrieved successfully');
        } catch (\Exception $e) {
            return $this->serverErrorResponse($e->getMessage());
        }
    }

    public function storeEbookFormat()
    {
        try {
            $data = $this->request->all();

            $requiredFields = ['name'];
            $errors = [];
            
            foreach ($requiredFields as $field) {
                if (empty($data[$field])) {
                    $errors[$field] = ["The {$field} field is required."];
                }
            }

            if (!empty($errors)) {
                return $this->validationErrorResponse($errors);
            }

            $format = EbookFormat::create($data);

            return $this->successResponse($format, 'Ebook format created successfully', 201);
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 'EBOOK_FORMAT_CREATION_ERROR', null, 400);
        }
    }

    public function showEbookFormat(string $id)
    {
        try {
            $format = EbookFormat::find($id);

            if (!$format) {
                return $this->notFoundResponse('Ebook format not found');
            }

            return $this->successResponse($format, 'Ebook format retrieved successfully');
        } catch (\Exception $e) {
            return $this->serverErrorResponse($e->getMessage());
        }
    }

    public function updateEbookFormat(string $id)
    {
        try {
            $format = EbookFormat::find($id);

            if (!$format) {
                return $this->notFoundResponse('Ebook format not found');
            }

            $data = $this->request->all();
            $format->update($data);

            return $this->successResponse($format, 'Ebook format updated successfully');
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 'EBOOK_FORMAT_UPDATE_ERROR', null, 400);
        }
    }

    public function destroyEbookFormat(string $id)
    {
        try {
            $format = EbookFormat::find($id);

            if (!$format) {
                return $this->notFoundResponse('Ebook format not found');
            }

            $format->delete();

            return $this->successResponse(null, 'Ebook format deleted successfully');
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 'EBOOK_FORMAT_DELETION_ERROR', null, 400);
        }
    }
}
