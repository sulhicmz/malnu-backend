<?php

declare(strict_types = 1);

namespace App\Http\Controllers\Api\FeeManagement;

use App\Http\Controllers\Api\BaseController;
use App\Services\FeeManagementService;
use Hyperf\HttpServer\Contract\RequestInterface;
use Hyperf\HttpServer\Contract\ResponseInterface;
use Psr\Container\ContainerInterface;

class FeeManagementController extends BaseController
{
    private FeeManagementService $feeService;

    public function __construct(
        RequestInterface $request,
        ResponseInterface $response,
        ContainerInterface $container,
        FeeManagementService $feeService
    ) {
        parent::__construct($request, $response, $container);
        $this->feeService = $feeService;
    }

    public function feeTypesIndex()
    {
        try {
            $query = \App\Models\FeeManagement\FeeType::query();

            $category = $this->request->query('category');
            $isActive = $this->request->query('is_active');
            $page = (int) $this->request->query('page', 1);
            $limit = (int) $this->request->query('limit', 15);

            if ($category) {
                $query->where('category', $category);
            }

            if ($isActive !== null) {
                $query->where('is_active', $isActive === 'true');
            }

            $feeTypes = $query->orderBy('name')->paginate($limit, ['*'], 'page', $page);

            return $this->successResponse($feeTypes, 'Fee types retrieved successfully');
        } catch (\Exception $e) {
            return $this->serverErrorResponse($e->getMessage());
        }
    }

    public function feeTypesStore()
    {
        try {
            $data = $this->request->all();
            $errors = [];

            $requiredFields = ['name', 'code', 'category'];
            foreach ($requiredFields as $field) {
                if (empty($data[$field])) {
                    $errors[$field] = ["The {$field} field is required."];
                }
            }

            if (!empty($errors)) {
                return $this->validationErrorResponse($errors);
            }

            $feeType = $this->feeService->createFeeType($data);

            return $this->successResponse($feeType, 'Fee type created successfully', 201);
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 'FEE_TYPE_CREATION_ERROR', null, 400);
        }
    }

    public function feeTypesShow(string $id)
    {
        try {
            $feeType = \App\Models\FeeManagement\FeeType::with('feeStructures')->find($id);

            if (!$feeType) {
                return $this->notFoundResponse('Fee type not found');
            }

            return $this->successResponse($feeType, 'Fee type retrieved successfully');
        } catch (\Exception $e) {
            return $this->serverErrorResponse($e->getMessage());
        }
    }

    public function feeTypesUpdate(string $id)
    {
        try {
            $data = $this->request->all();
            $feeType = $this->feeService->updateFeeType($id, $data);

            return $this->successResponse($feeType, 'Fee type updated successfully');
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 'FEE_TYPE_UPDATE_ERROR', null, 400);
        }
    }

    public function feeTypesDestroy(string $id)
    {
        try {
            $this->feeService->deleteFeeType($id);

            return $this->successResponse(null, 'Fee type deleted successfully');
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 'FEE_TYPE_DELETION_ERROR', null, 400);
        }
    }

    public function feeStructuresIndex()
    {
        try {
            $query = \App\Models\FeeManagement\FeeStructure::with('feeType');

            $gradeLevel = $this->request->query('grade_level');
            $academicYear = $this->request->query('academic_year');
            $isActive = $this->request->query('is_active');
            $page = (int) $this->request->query('page', 1);
            $limit = (int) $this->request->query('limit', 15);

            if ($gradeLevel) {
                $query->where('grade_level', $gradeLevel);
            }

            if ($academicYear) {
                $query->where('academic_year', $academicYear);
            }

            if ($isActive !== null) {
                $query->where('is_active', $isActive === 'true');
            }

            $structures = $query->orderBy('grade_level')->orderBy('academic_year')->paginate($limit, ['*'], 'page', $page);

            return $this->successResponse($structures, 'Fee structures retrieved successfully');
        } catch (\Exception $e) {
            return $this->serverErrorResponse($e->getMessage());
        }
    }

    public function feeStructuresStore()
    {
        try {
            $data = $this->request->all();
            $errors = [];

            $requiredFields = ['fee_type_id', 'grade_level', 'academic_year', 'amount'];
            foreach ($requiredFields as $field) {
                if (empty($data[$field])) {
                    $errors[$field] = ["The {$field} field is required."];
                }
            }

            if (!empty($errors)) {
                return $this->validationErrorResponse($errors);
            }

            $structure = $this->feeService->createFeeStructure($data);

            return $this->successResponse($structure, 'Fee structure created successfully', 201);
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 'FEE_STRUCTURE_CREATION_ERROR', null, 400);
        }
    }

    public function feeStructuresShow(string $id)
    {
        try {
            $structure = \App\Models\FeeManagement\FeeStructure::with('feeType')->find($id);

            if (!$structure) {
                return $this->notFoundResponse('Fee structure not found');
            }

            return $this->successResponse($structure, 'Fee structure retrieved successfully');
        } catch (\Exception $e) {
            return $this->serverErrorResponse($e->getMessage());
        }
    }

    public function feeStructuresUpdate(string $id)
    {
        try {
            $data = $this->request->all();
            $structure = $this->feeService->updateFeeStructure($id, $data);

            return $this->successResponse($structure, 'Fee structure updated successfully');
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 'FEE_STRUCTURE_UPDATE_ERROR', null, 400);
        }
    }

    public function feeStructuresDestroy(string $id)
    {
        try {
            $this->feeService->deleteFeeStructure($id);

            return $this->successResponse(null, 'Fee structure deleted successfully');
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 'FEE_STRUCTURE_DELETION_ERROR', null, 400);
        }
    }

    public function invoicesIndex()
    {
        try {
            $query = \App\Models\FeeManagement\FeeInvoice::with(['student', 'feeStructure.feeType']);

            $studentId = $this->request->query('student_id');
            $status = $this->request->query('status');
            $overdue = $this->request->query('overdue');
            $page = (int) $this->request->query('page', 1);
            $limit = (int) $this->request->query('limit', 15);

            if ($studentId) {
                $query->where('student_id', $studentId);
            }

            if ($status) {
                $query->where('status', $status);
            }

            if ($overdue === 'true') {
                $query->overdue();
            }

            $invoices = $query->orderBy('issue_date', 'desc')->paginate($limit, ['*'], 'page', $page);

            return $this->successResponse($invoices, 'Invoices retrieved successfully');
        } catch (\Exception $e) {
            return $this->serverErrorResponse($e->getMessage());
        }
    }

    public function invoicesStore()
    {
        try {
            $data = $this->request->all();
            $errors = [];

            $requiredFields = ['student_id', 'fee_structure_id'];
            foreach ($requiredFields as $field) {
                if (empty($data[$field])) {
                    $errors[$field] = ["The {$field} field is required."];
                }
            }

            if (!empty($errors)) {
                return $this->validationErrorResponse($errors);
            }

            $invoice = $this->feeService->generateInvoice($data);

            return $this->successResponse($invoice, 'Invoice generated successfully', 201);
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 'INVOICE_GENERATION_ERROR', null, 400);
        }
    }

    public function invoicesGenerateBulk()
    {
        try {
            $data = $this->request->all();
            $errors = [];

            $requiredFields = ['grade', 'academic_year'];
            foreach ($requiredFields as $field) {
                if (empty($data[$field])) {
                    $errors[$field] = ["The {$field} field is required."];
                }
            }

            if (!empty($errors)) {
                return $this->validationErrorResponse($errors);
            }

            $invoices = $this->feeService->generateInvoicesForGrade($data['grade'], $data['academic_year']);

            return $this->successResponse([
                'count' => count($invoices),
                'invoices' => $invoices
            ], 'Bulk invoices generated successfully', 201);
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 'BULK_INVOICE_GENERATION_ERROR', null, 400);
        }
    }

    public function invoicesShow(string $id)
    {
        try {
            $invoice = \App\Models\FeeManagement\FeeInvoice::with(['student', 'feeStructure.feeType', 'payments', 'waivers'])->find($id);

            if (!$invoice) {
                return $this->notFoundResponse('Invoice not found');
            }

            return $this->successResponse($invoice, 'Invoice retrieved successfully');
        } catch (\Exception $e) {
            return $this->serverErrorResponse($e->getMessage());
        }
    }

    public function paymentsIndex()
    {
        try {
            $query = \App\Models\FeeManagement\FeePayment::with(['invoice.student', 'user']);

            $invoiceId = $this->request->query('invoice_id');
            $status = $this->request->query('status');
            $page = (int) $this->request->query('page', 1);
            $limit = (int) $this->request->query('limit', 15);

            if ($invoiceId) {
                $query->where('invoice_id', $invoiceId);
            }

            if ($status) {
                $query->where('status', $status);
            }

            $payments = $query->orderBy('created_at', 'desc')->paginate($limit, ['*'], 'page', $page);

            return $this->successResponse($payments, 'Payments retrieved successfully');
        } catch (\Exception $e) {
            return $this->serverErrorResponse($e->getMessage());
        }
    }

    public function paymentsStore()
    {
        try {
            $data = $this->request->all();
            $errors = [];

            $requiredFields = ['invoice_id', 'user_id', 'payment_method', 'amount'];
            foreach ($requiredFields as $field) {
                if (empty($data[$field])) {
                    $errors[$field] = ["The {$field} field is required."];
                }
            }

            if (!empty($errors)) {
                return $this->validationErrorResponse($errors);
            }

            if ($data['amount'] <= 0) {
                $errors['amount'] = ['The amount must be greater than 0.'];
                return $this->validationErrorResponse($errors);
            }

            $payment = $this->feeService->createPayment($data);

            return $this->successResponse($payment, 'Payment created successfully', 201);
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 'PAYMENT_CREATION_ERROR', null, 400);
        }
    }

    public function paymentsShow(string $id)
    {
        try {
            $payment = \App\Models\FeeManagement\FeePayment::with(['invoice.student', 'user'])->find($id);

            if (!$payment) {
                return $this->notFoundResponse('Payment not found');
            }

            return $this->successResponse($payment, 'Payment retrieved successfully');
        } catch (\Exception $e) {
            return $this->serverErrorResponse($e->getMessage());
        }
    }

    public function waiversIndex()
    {
        try {
            $query = \App\Models\FeeManagement\FeeWaiver::with(['student', 'invoice', 'approvedBy']);

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

            $waivers = $query->orderBy('created_at', 'desc')->paginate($limit, ['*'], 'page', $page);

            return $this->successResponse($waivers, 'Waivers retrieved successfully');
        } catch (\Exception $e) {
            return $this->serverErrorResponse($e->getMessage());
        }
    }

    public function waiversStore()
    {
        try {
            $data = $this->request->all();
            $errors = [];

            $requiredFields = ['student_id', 'waiver_type', 'waiver_code', 'reason'];
            foreach ($requiredFields as $field) {
                if (empty($data[$field])) {
                    $errors[$field] = ["The {$field} field is required."];
                }
            }

            if (!empty($errors)) {
                return $this->validationErrorResponse($errors);
            }

            $waiver = $this->feeService->createWaiver($data);

            return $this->successResponse($waiver, 'Waiver created successfully', 201);
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 'WAIVER_CREATION_ERROR', null, 400);
        }
    }

    public function reportsFinancial()
    {
        try {
            $filters = [
                'from_date' => $this->request->query('from_date'),
                'to_date' => $this->request->query('to_date'),
                'status' => $this->request->query('status'),
            ];

            $report = $this->feeService->getFinancialReport($filters);

            return $this->successResponse($report, 'Financial report generated successfully');
        } catch (\Exception $e) {
            return $this->serverErrorResponse($e->getMessage());
        }
    }

    public function studentOutstanding(string $studentId)
    {
        try {
            $outstanding = $this->feeService->getStudentOutstandingBalance($studentId);

            return $this->successResponse($outstanding, 'Student outstanding balance retrieved successfully');
        } catch (\Exception $e) {
            return $this->serverErrorResponse($e->getMessage());
        }
    }
}
