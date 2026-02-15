<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\FinancialManagement;

use App\Http\Controllers\Api\BaseController;
use App\Models\FinancialManagement\FeeStructure;
use App\Models\FinancialManagement\Invoice;
use App\Models\FinancialManagement\InvoiceItem;
use Hypervel\Support\Facades\DB;
use Hypervel\Http\Request;
use Hypervel\Http\Response;
use Psr\Container\ContainerInterface;
use OpenApi\Annotations as OA;

class InvoiceController extends BaseController
{
    protected string $resourceName = 'Invoice';

    public function __construct(
        RequestInterface $request,
        ResponseInterface $response,
        ContainerInterface $container
    ) {
        parent::__construct($request, $response, $container);
    }

    public function index()
    {
        try {
            $query = Invoice::query();

            $page = (int) $this->request->query('page', 1);
            $limit = (int) $this->request->query('limit', 15);

            if ($studentId = $this->request->query('student_id')) {
                $query->where('student_id', $studentId);
            }

            if ($status = $this->request->query('status')) {
                $query->where('status', $status);
            }

            $results = $query->with(['student', 'feeStructure', 'invoiceItems', 'payments'])
                ->orderBy('created_at', 'desc')
                ->paginate($limit, ['*'], 'page', $page);

            return $this->successResponse($results, 'Invoices retrieved successfully');
        } catch (\Exception $e) {
            return $this->serverErrorResponse($e->getMessage());
        }
    }

    public function show(string $id)
    {
        try {
            $invoice = Invoice::with(['student', 'feeStructure', 'invoiceItems.feeType', 'payments'])
                ->find($id);

            if (! $invoice) {
                return $this->notFoundResponse('Invoice not found');
            }

            return $this->successResponse($invoice, 'Invoice retrieved successfully');
        } catch (\Exception $e) {
            return $this->serverErrorResponse($e->getMessage());
        }
    }

    public function store()
    {
        try {
            $data = $this->request->all();

            $errors = $this->validateInvoiceData($data);

            if (! empty($errors)) {
                return $this->validationErrorResponse($errors);
            }

            $feeStructure = FeeStructure::find($data['fee_structure_id']);

            if (! $feeStructure) {
                return $this->errorResponse('Fee structure not found', 'FEE_STRUCTURE_NOT_FOUND', 404);
            }

            Db::beginTransaction();

            $invoiceNumber = $this->generateInvoiceNumber();

            $subtotal = $feeStructure->amount;

            $invoice = Invoice::create([
                'student_id' => $data['student_id'],
                'fee_structure_id' => $data['fee_structure_id'],
                'invoice_number' => $invoiceNumber,
                'subtotal' => $subtotal,
                'discount_amount' => $data['discount_amount'] ?? 0,
                'tax_amount' => $data['tax_amount'] ?? 0,
                'total_amount' => $subtotal - ($data['discount_amount'] ?? 0) + ($data['tax_amount'] ?? 0),
                'paid_amount' => 0,
                'status' => 'unpaid',
                'issue_date' => $data['issue_date'] ?? date('Y-m-d'),
                'due_date' => $data['due_date'] ?? $feeStructure->due_date ?? date('Y-m-d', strtotime('+30 days')),
                'notes' => $data['notes'] ?? null,
            ]);

            InvoiceItem::create([
                'invoice_id' => $invoice->id,
                'fee_type_id' => $feeStructure->fee_type_id,
                'description' => $feeStructure->name,
                'quantity' => 1,
                'unit_price' => $feeStructure->amount,
                'discount_amount' => $data['discount_amount'] ?? 0,
                'amount' => $feeStructure->amount,
            ]);

            Db::commit();

            return $this->successResponse($invoice->load(['invoiceItems.feeType']), 'Invoice created successfully', 201);
        } catch (\Exception $e) {
            Db::rollBack();
            return $this->serverErrorResponse($e->getMessage());
        }
    }

    public function update(string $id)
    {
        try {
            $invoice = Invoice::find($id);

            if (! $invoice) {
                return $this->notFoundResponse('Invoice not found');
            }

            if ($invoice->payments()->count() > 0) {
                return $this->errorResponse('Cannot update invoice with payments', 'INVOICE_HAS_PAYMENTS', 400);
            }

            $data = $this->request->all();

            $errors = $this->validateInvoiceData($data, true);

            if (! empty($errors)) {
                return $this->validationErrorResponse($errors);
            }

            $invoice->update([
                'discount_amount' => $data['discount_amount'] ?? $invoice->discount_amount,
                'tax_amount' => $data['tax_amount'] ?? $invoice->tax_amount,
                'total_amount' => $invoice->subtotal - ($data['discount_amount'] ?? $invoice->discount_amount) + ($data['tax_amount'] ?? $invoice->tax_amount),
                'due_date' => $data['due_date'] ?? $invoice->due_date,
                'notes' => $data['notes'] ?? $invoice->notes,
            ]);

            return $this->successResponse($invoice, 'Invoice updated successfully');
        } catch (\Exception $e) {
            return $this->serverErrorResponse($e->getMessage());
        }
    }

    public function destroy(string $id)
    {
        try {
            $invoice = Invoice::find($id);

            if (! $invoice) {
                return $this->notFoundResponse('Invoice not found');
            }

            if ($invoice->payments()->count() > 0) {
                return $this->errorResponse('Cannot delete invoice with payments', 'INVOICE_HAS_PAYMENTS', 400);
            }

            $invoice->delete();

            return $this->successResponse(null, 'Invoice deleted successfully');
        } catch (\Exception $e) {
            return $this->serverErrorResponse($e->getMessage());
        }
    }

    protected function validateInvoiceData(array $data, bool $isUpdate = false): array
    {
        $errors = [];

        if (! $isUpdate && empty($data['student_id'])) {
            $errors[] = 'Student ID is required';
        }

        if (! $isUpdate && empty($data['fee_structure_id'])) {
            $errors[] = 'Fee structure ID is required';
        }

        if (isset($data['discount_amount']) && $data['discount_amount'] < 0) {
            $errors[] = 'Discount amount must be non-negative';
        }

        if (isset($data['tax_amount']) && $data['tax_amount'] < 0) {
            $errors[] = 'Tax amount must be non-negative';
        }

        if (! $isUpdate && !empty($data['due_date']) && !strtotime($data['due_date'])) {
            $errors[] = 'Invalid due date';
        }

        return $errors;
    }

    protected function generateInvoiceNumber(): string
    {
        $prefix = 'INV';
        $date = date('Ymd');
        $sequence = str_pad((string) rand(1, 9999), 4, '0', STR_PAD_LEFT);
        
        return "{$prefix}{$date}{$sequence}";
    }
}
