<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Services\LibraryManagementService;
use App\Traits\InputValidationTrait;

class LibraryManagementController extends BaseController
{
    use InputValidationTrait;

    private LibraryManagementService $libraryService;

    public function __construct()
    {
        parent::__construct();
        $this->libraryService = new LibraryManagementService();
    }

    public function createPatron()
    {
        try {
            $data = $this->request->all();
            $data = $this->sanitizeInput($data);

            $errors = $this->validatePatronData($data);
            if (!empty($errors)) {
                return $this->validationErrorResponse($errors);
            }

            $patron = $this->libraryService->createPatron($data);

            return $this->successResponse($patron, 'Patron created successfully');
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 'PATRON_CREATE_ERROR', null, 400);
        }
    }

    public function getPatrons()
    {
        try {
            $filters = $this->request->all();
            $patrons = $this->libraryService->getAllPatrons($filters);

            return $this->successResponse($patrons, 'Patrons retrieved successfully');
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 'PATRONS_FETCH_ERROR', null, 500);
        }
    }

    public function getPatron($id)
    {
        try {
            $patron = $this->libraryService->getPatron($id);
            if (!$patron) {
                return $this->errorResponse('Patron not found', 'PATRON_NOT_FOUND', null, 404);
            }

            return $this->successResponse($patron, 'Patron retrieved successfully');
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 'PATRON_FETCH_ERROR', null, 500);
        }
    }

    public function updatePatron($id)
    {
        try {
            $data = $this->request->all();
            $data = $this->sanitizeInput($data);

            $errors = $this->validatePatronUpdateData($data);
            if (!empty($errors)) {
                return $this->validationErrorResponse($errors);
            }

            $success = $this->libraryService->updatePatron($id, $data);
            if (!$success) {
                return $this->errorResponse('Patron not found', 'PATRON_NOT_FOUND', null, 404);
            }

            return $this->successResponse(null, 'Patron updated successfully');
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 'PATRON_UPDATE_ERROR', null, 400);
        }
    }

    public function deletePatron($id)
    {
        try {
            $success = $this->libraryService->deletePatron($id);
            if (!$success) {
                return $this->errorResponse('Patron not found', 'PATRON_NOT_FOUND', null, 404);
            }

            return $this->successResponse(null, 'Patron deleted successfully');
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 'PATRON_DELETE_ERROR', null, 400);
        }
    }

    public function checkoutBook()
    {
        try {
            $data = $this->request->all();
            $data = $this->sanitizeInput($data);

            $errors = $this->validateCheckoutData($data);
            if (!empty($errors)) {
                return $this->validationErrorResponse($errors);
            }

            $loan = $this->libraryService->checkoutBook(
                $data['patron_id'],
                $data['book_id'],
                $data['loan_days'] ?? 14
            );

            return $this->successResponse($loan, 'Book checked out successfully');
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 'CHECKOUT_ERROR', null, 400);
        }
    }

    public function returnBook($loanId)
    {
        try {
            $loan = $this->libraryService->returnBook($loanId);

            return $this->successResponse($loan, 'Book returned successfully');
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 'RETURN_ERROR', null, 400);
        }
    }

    public function renewBook($loanId)
    {
        try {
            $data = $this->request->all();
            $loan = $this->libraryService->renewBook($loanId, $data['additional_days'] ?? 14);

            return $this->successResponse($loan, 'Book renewed successfully');
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 'RENEW_ERROR', null, 400);
        }
    }

    public function placeHold()
    {
        try {
            $data = $this->request->all();
            $data = $this->sanitizeInput($data);

            $errors = $this->validateHoldData($data);
            if (!empty($errors)) {
                return $this->validationErrorResponse($errors);
            }

            $hold = $this->libraryService->placeHold(
                $data['patron_id'],
                $data['book_id'],
                $data['hold_type'] ?? 'hold'
            );

            return $this->successResponse($hold, 'Hold placed successfully');
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 'HOLD_ERROR', null, 400);
        }
    }

    public function cancelHold($holdId)
    {
        try {
            $success = $this->libraryService->cancelHold($holdId);

            return $this->successResponse(null, 'Hold cancelled successfully');
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 'HOLD_CANCEL_ERROR', null, 400);
        }
    }

    public function fulfillHold($holdId)
    {
        try {
            $hold = $this->libraryService->fulfillHold($holdId);

            return $this->successResponse($hold, 'Hold fulfilled successfully');
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 'HOLD_FULFILL_ERROR', null, 400);
        }
    }

    public function createFine()
    {
        try {
            $data = $this->request->all();
            $data = $this->sanitizeInput($data);

            $errors = $this->validateFineData($data);
            if (!empty($errors)) {
                return $this->validationErrorResponse($errors);
            }

            $fine = $this->libraryService->createFine($data);

            return $this->successResponse($fine, 'Fine created successfully');
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 'FINE_CREATE_ERROR', null, 400);
        }
    }

    public function payFine($fineId)
    {
        try {
            $data = $this->request->all();
            $fine = $this->libraryService->payFine($fineId, $data['amount']);

            return $this->successResponse($fine, 'Fine payment recorded successfully');
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 'FINE_PAYMENT_ERROR', null, 400);
        }
    }

    public function waiveFine($fineId)
    {
        try {
            $fine = $this->libraryService->waiveFine($fineId);

            return $this->successResponse($fine, 'Fine waived successfully');
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 'FINE_WAIVE_ERROR', null, 400);
        }
    }

    public function createInventoryRecord()
    {
        try {
            $data = $this->request->all();
            $data = $this->sanitizeInput($data);

            $errors = $this->validateInventoryData($data);
            if (!empty($errors)) {
                return $this->validationErrorResponse($errors);
            }

            $inventory = $this->libraryService->createInventoryRecord($data);

            return $this->successResponse($inventory, 'Inventory record created successfully');
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 'INVENTORY_CREATE_ERROR', null, 400);
        }
    }

    public function createAcquisition()
    {
        try {
            $data = $this->request->all();
            $data = $this->sanitizeInput($data);

            $errors = $this->validateAcquisitionData($data);
            if (!empty($errors)) {
                return $this->validationErrorResponse($errors);
            }

            $acquisition = $this->libraryService->createAcquisition($data);

            return $this->successResponse($acquisition, 'Acquisition created successfully');
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 'ACQUISITION_CREATE_ERROR', null, 400);
        }
    }

    public function markAcquisitionReceived($id)
    {
        try {
            $data = $this->request->all();
            $acquisition = $this->libraryService->markAcquisitionReceived($id, $data);

            return $this->successResponse($acquisition, 'Acquisition marked as received');
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 'ACQUISITION_RECEIVE_ERROR', null, 400);
        }
    }

    public function createReadingProgram()
    {
        try {
            $data = $this->request->all();
            $data = $this->sanitizeInput($data);

            $errors = $this->validateReadingProgramData($data);
            if (!empty($errors)) {
                return $this->validationErrorResponse($errors);
            }

            $program = $this->libraryService->createReadingProgram($data);

            return $this->successResponse($program, 'Reading program created successfully');
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 'PROGRAM_CREATE_ERROR', null, 400);
        }
    }

    public function enrollInProgram()
    {
        try {
            $data = $this->request->all();
            $data = $this->sanitizeInput($data);

            $errors = $this->validateEnrollmentData($data);
            if (!empty($errors)) {
                return $this->validationErrorResponse($errors);
            }

            $participant = $this->libraryService->enrollInProgram(
                $data['program_id'],
                $data['patron_id']
            );

            return $this->successResponse($participant, 'Enrolled in program successfully');
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 'ENROLLMENT_ERROR', null, 400);
        }
    }

    public function recordBooksRead($participantId)
    {
        try {
            $data = $this->request->all();
            $participant = $this->libraryService->recordBooksRead(
                $participantId,
                $data['count'] ?? 1
            );

            return $this->successResponse($participant, 'Books recorded successfully');
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 'BOOKS_READ_ERROR', null, 400);
        }
    }

    public function createSpace()
    {
        try {
            $data = $this->request->all();
            $data = $this->sanitizeInput($data);

            $errors = $this->validateSpaceData($data);
            if (!empty($errors)) {
                return $this->validationErrorResponse($errors);
            }

            $space = $this->libraryService->createSpace($data);

            return $this->successResponse($space, 'Library space created successfully');
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 'SPACE_CREATE_ERROR', null, 400);
        }
    }

    public function bookSpace()
    {
        try {
            $data = $this->request->all();
            $data = $this->sanitizeInput($data);

            $errors = $this->validateSpaceBookingData($data);
            if (!empty($errors)) {
                return $this->validationErrorResponse($errors);
            }

            $booking = $this->libraryService->bookSpace($data);

            return $this->successResponse($booking, 'Space booked successfully');
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 'SPACE_BOOKING_ERROR', null, 400);
        }
    }

    public function cancelSpaceBooking($bookingId)
    {
        try {
            $booking = $this->libraryService->cancelSpaceBooking($bookingId);

            return $this->successResponse($booking, 'Space booking cancelled successfully');
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 'BOOKING_CANCEL_ERROR', null, 400);
        }
    }

    public function createMarcRecord()
    {
        try {
            $data = $this->request->all();
            $data = $this->sanitizeInput($data);

            $errors = $this->validateMarcData($data);
            if (!empty($errors)) {
                return $this->validationErrorResponse($errors);
            }

            $record = $this->libraryService->createMarcRecord($data);

            return $this->successResponse($record, 'MARC record created successfully');
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 'MARC_CREATE_ERROR', null, 400);
        }
    }

    public function addMarcField($recordId)
    {
        try {
            $data = $this->request->all();
            $data = $this->sanitizeInput($data);

            $errors = $this->validateMarcFieldData($data);
            if (!empty($errors)) {
                return $this->validationErrorResponse($errors);
            }

            $field = $this->libraryService->addMarcField($recordId, $data);

            return $this->successResponse($field, 'MARC field added successfully');
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 'MARC_FIELD_ERROR', null, 400);
        }
    }

    public function recordAnalytics()
    {
        try {
            $data = $this->request->all();
            $analytics = $this->libraryService->recordAnalytics($data['date'] ?? null);

            return $this->successResponse($analytics, 'Analytics recorded successfully');
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 'ANALYTICS_ERROR', null, 400);
        }
    }

    public function getPopularBooks()
    {
        try {
            $data = $this->request->all();
            $books = $this->libraryService->getPopularBooks(
                $data['limit'] ?? 10,
                $data['days'] ?? 30
            );

            return $this->successResponse($books, 'Popular books retrieved successfully');
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 'POPULAR_BOOKS_ERROR', null, 500);
        }
    }

    public function getPatronReadingHistory($patronId)
    {
        try {
            $filters = $this->request->all();
            $history = $this->libraryService->getPatronReadingHistory($patronId, $filters);

            return $this->successResponse($history, 'Reading history retrieved successfully');
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 'READING_HISTORY_ERROR', null, 500);
        }
    }

    public function generateOverdueFines()
    {
        try {
            $count = $this->libraryService->generateOverdueFines();

            return $this->successResponse(['count' => $count], 'Overdue fines generated successfully');
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 'OVERDUE_FINES_ERROR', null, 500);
        }
    }

    protected function validatePatronData(array $data): array
    {
        $errors = [];
        $requiredFields = ['user_id', 'library_card_number'];

        foreach ($requiredFields as $field) {
            if (!isset($data[$field]) || empty($data[$field])) {
                $errors[$field] = ["The {$field} field is required."];
            }
        }

        return $errors;
    }

    protected function validatePatronUpdateData(array $data): array
    {
        $errors = [];

        if (isset($data['library_card_number']) && !$this->validateStringLength($data['library_card_number'], 5)) {
            $errors['library_card_number'] = ['Library card number must be at least 5 characters.'];
        }

        return $errors;
    }

    protected function validateCheckoutData(array $data): array
    {
        $errors = [];
        $requiredFields = ['patron_id', 'book_id'];

        foreach ($requiredFields as $field) {
            if (!isset($data[$field]) || empty($data[$field])) {
                $errors[$field] = ["The {$field} field is required."];
            }
        }

        return $errors;
    }

    protected function validateHoldData(array $data): array
    {
        $errors = [];
        $requiredFields = ['patron_id', 'book_id'];

        foreach ($requiredFields as $field) {
            if (!isset($data[$field]) || empty($data[$field])) {
                $errors[$field] = ["The {$field} field is required."];
            }
        }

        return $errors;
    }

    protected function validateFineData(array $data): array
    {
        $errors = [];
        $requiredFields = ['patron_id', 'fine_type', 'amount'];

        foreach ($requiredFields as $field) {
            if (!isset($data[$field]) || empty($data[$field])) {
                $errors[$field] = ["The {$field} field is required."];
            }
        }

        return $errors;
    }

    protected function validateInventoryData(array $data): array
    {
        $errors = [];
        $requiredFields = ['book_id', 'action_type', 'expected_quantity', 'actual_quantity'];

        foreach ($requiredFields as $field) {
            if (!isset($data[$field])) {
                $errors[$field] = ["The {$field} field is required."];
            }
        }

        return $errors;
    }

    protected function validateAcquisitionData(array $data): array
    {
        $errors = [];
        $requiredFields = ['title', 'quantity', 'unit_cost'];

        foreach ($requiredFields as $field) {
            if (!isset($data[$field]) || empty($data[$field])) {
                $errors[$field] = ["The {$field} field is required."];
            }
        }

        return $errors;
    }

    protected function validateReadingProgramData(array $data): array
    {
        $errors = [];
        $requiredFields = ['program_name', 'program_type', 'start_date'];

        foreach ($requiredFields as $field) {
            if (!isset($data[$field]) || empty($data[$field])) {
                $errors[$field] = ["The {$field} field is required."];
            }
        }

        return $errors;
    }

    protected function validateEnrollmentData(array $data): array
    {
        $errors = [];
        $requiredFields = ['program_id', 'patron_id'];

        foreach ($requiredFields as $field) {
            if (!isset($data[$field]) || empty($data[$field])) {
                $errors[$field] = ["The {$field} field is required."];
            }
        }

        return $errors;
    }

    protected function validateSpaceData(array $data): array
    {
        $errors = [];
        $requiredFields = ['space_name', 'space_type', 'capacity'];

        foreach ($requiredFields as $field) {
            if (!isset($data[$field]) || empty($data[$field])) {
                $errors[$field] = ["The {$field} field is required."];
            }
        }

        return $errors;
    }

    protected function validateSpaceBookingData(array $data): array
    {
        $errors = [];
        $requiredFields = ['space_id', 'user_id', 'start_time', 'end_time'];

        foreach ($requiredFields as $field) {
            if (!isset($data[$field]) || empty($data[$field])) {
                $errors[$field] = ["The {$field} field is required."];
            }
        }

        return $errors;
    }

    protected function validateMarcData(array $data): array
    {
        $errors = [];
        $requiredFields = ['book_id', 'record_type'];

        foreach ($requiredFields as $field) {
            if (!isset($data[$field]) || empty($data[$field])) {
                $errors[$field] = ["The {$field} field is required."];
            }
        }

        return $errors;
    }

    protected function validateMarcFieldData(array $data): array
    {
        $errors = [];
        $requiredFields = ['tag'];

        if (!isset($data['tag']) || empty($data['tag'])) {
            $errors['tag'] = ["The tag field is required."];
        }

        return $errors;
    }
}
