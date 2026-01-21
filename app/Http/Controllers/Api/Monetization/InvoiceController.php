<?php

namespace App\Http\Controllers\Api\Monetization;

use App\Http\Controllers\Api\BaseController;
use App\Models\Monetization\Invoice;
use App\Models\Monetization\FeeStructure;
use App\Models\Monetization\InvoiceItem;
use App\Models\User;
use Hyperf\HttpServer\Contract\RequestInterface;
use Hyperf\HttpServer\Contract\ResponseInterface;
use Psr\Container\ContainerInterface;
use OpenApi\Annotations as OA;

/**
 * @OA\Tag(
 *     name="Invoice",
 *     description="Invoice management endpoints"
 * )
 */
class InvoiceController extends BaseController
{
    private Invoice $invoiceModel;
    private FeeStructure $feeStructureModel;
    private InvoiceItem $invoiceItemModel;
    private User $userModel;

    public function __construct(
        RequestInterface $request,
        ResponseInterface $response,
        ContainerInterface $container,
        Invoice $invoiceModel,
        FeeStructure $feeStructureModel,
        InvoiceItem $invoiceItemModel,
        User $userModel
    ) {
        parent::__construct($request, $response, $container);
        $this->invoiceModel = $invoiceModel;
        $this->feeStructureModel = $feeStructureModel;
        $this->invoiceItemModel = $invoiceItemModel;
        $this->userModel = $userModel;
    }

    /**
     * @OA\Get(
     *     path="/api/monetization/invoices",
     *     tags={"Invoice"},
     *     summary="List all invoices",
     *     @OA\Parameter(
     *         name="student_id",
     *         in="query",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="status",
     *         in="query",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="academic_year",
     *         in="query",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="List of invoices"
     *     )
     * )
     */
    public function index()
    {
        $query = $this->invoiceModel->newQuery();

        $studentId = $this->request->input('student_id');
        if ($studentId) {
            $query->where('student_id', $studentId);
        }

        $status = $this->request->input('status');
        if ($status) {
            $query->where('status', $status);
        }

        $academicYear = $this->request->input('academic_year');
        if ($academicYear) {
            $query->whereHas('feeStructure', function ($q) use ($academicYear) {
                $q->where('academic_year', $academicYear);
            });
        }

        $page = (int)($this->request->input('page', 1));
        $perPage = (int)($this->request->input('per_page', 15));

        $invoices = $query->with(['student', 'feeStructure', 'invoiceItems'])
            ->orderBy('created_at', 'desc')
            ->offset(($page - 1) * $perPage)
            ->limit($perPage)
            ->get();

        $total = $query->count();

        return $this->successResponse([
            'invoices' => $invoices,
            'pagination' => [
                'page' => $page,
                'per_page' => $perPage,
                'total' => $total,
                'total_pages' => (int)ceil($total / $perPage),
            ],
        ], 'Invoices retrieved successfully');
    }

    /**
     * @OA\Get(
     *     path="/api/monetization/invoices/{id}",
     *     tags={"Invoice"},
     *     summary="Get invoice by ID",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Invoice details"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Invoice not found"
     *     )
     * )
     */
    public function show(string $id)
    {
        $invoice = $this->invoiceModel->with(['student', 'feeStructure', 'invoiceItems', 'payments'])
            ->find($id);

        if (!$invoice) {
            return $this->errorResponse('Invoice not found', 'INVOICE_NOT_FOUND');
        }

        return $this->successResponse($invoice, 'Invoice retrieved successfully');
    }

    /**
     * @OA\Post(
     *     path="/api/monetization/invoices",
     *     tags={"Invoice"},
     *     summary="Create new invoice",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"student_id", "fee_structure_id"},
     *             @OA\Property(property="student_id", type="string", example="uuid-of-student"),
     *             @OA\Property(property="fee_structure_id", type="string", example="uuid-of-fee-structure"),
     *             @OA\Property(property="issue_date", type="string", format="date", example="2026-01-20"),
     *             @OA\Property(property="due_date", type="string", format="date", example="2026-02-20"),
     *             @OA\Property(property="notes", type="string", example="Additional notes")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Invoice created successfully"
     *     )
     * )
     */
    public function store()
    {
        $data = $this->request->all();

        if (!$data['student_id'] ?? null) {
            return $this->errorResponse('Student ID is required', 'MISSING_STUDENT_ID');
        }

        if (!$data['fee_structure_id'] ?? null) {
            return $this->errorResponse('Fee structure ID is required', 'MISSING_FEE_STRUCTURE_ID');
        }

        $student = $this->userModel->find($data['student_id']);
        if (!$student) {
            return $this->errorResponse('Student not found', 'STUDENT_NOT_FOUND');
        }

        $feeStructure = $this->feeStructureModel->find($data['fee_structure_id']);
        if (!$feeStructure) {
            return $this->errorResponse('Fee structure not found', 'FEE_STRUCTURE_NOT_FOUND');
        }

        $invoiceData = [
            'student_id' => $data['student_id'],
            'fee_structure_id' => $data['fee_structure_id'],
            'invoice_number' => $this->generateInvoiceNumber(),
            'issue_date' => $data['issue_date'] ?? date('Y-m-d'),
            'due_date' => $data['due_date'] ?? $feeStructure->due_date,
            'subtotal' => $feeStructure->amount,
            'tax_amount' => 0,
            'discount_amount' => 0,
            'total_amount' => $feeStructure->amount,
            'status' => 'pending',
            'payment_status' => 'unpaid',
            'notes' => $data['notes'] ?? null,
        ];

        $invoice = $this->invoiceModel->create($invoiceData);

        $invoiceItemData = [
            'invoice_id' => $invoice->id,
            'fee_type_id' => $feeStructure->fee_type_id,
            'description' => $feeStructure->name,
            'quantity' => 1,
            'unit_price' => $feeStructure->amount,
            'amount' => $feeStructure->amount,
        ];

        $this->invoiceItemModel->create($invoiceItemData);

        return $this->successResponse($invoice->load(['student', 'feeStructure', 'invoiceItems']), 'Invoice created successfully');
    }

    /**
     * @OA\Put(
     *     path="/api/monetization/invoices/{id}",
     *     tags={"Invoice"},
     *     summary="Update invoice",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\RequestBody(
     *         required=false,
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="paid"),
     *             @OA\Property(property="payment_status", type="string", example="paid"),
     *             @OA\Property(property="notes", type="string", example="Updated notes")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Invoice updated successfully"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Invoice not found"
     *     )
     * )
     */
    public function update(string $id)
    {
        $invoice = $this->invoiceModel->find($id);
        if (!$invoice) {
            return $this->errorResponse('Invoice not found', 'INVOICE_NOT_FOUND', null, 404);
        }

        $data = $this->request->only(['status', 'payment_status', 'notes']);

        $invoice->update($data);

        return $this->successResponse($invoice, 'Invoice updated successfully');
    }

    /**
     * @OA\Delete(
     *     path="/api/monetization/invoices/{id}",
     *     tags={"Invoice"},
     *     summary="Delete invoice",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Invoice deleted successfully"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Invoice not found"
     *     )
     * )
     */
    public function destroy(string $id)
    {
        $invoice = $this->invoiceModel->find($id);
        if (!$invoice) {
            return $this->errorResponse('Invoice not found', 'INVOICE_NOT_FOUND', null, 404);
        }

        if ($invoice->payments()->exists()) {
            return $this->errorResponse('Cannot delete invoice with existing payments', 'INVOICE_HAS_PAYMENTS');
        }

        $invoice->delete();

        return $this->successResponse(null, 'Invoice deleted successfully');
    }

    private function generateInvoiceNumber(): string
    {
        $prefix = 'INV';
        $date = date('Ymd');
        $random = strtoupper(substr(md5(uniqid()), 0, 6));

        return sprintf('%s-%s-%s', $prefix, $date, $random);
    }
}
