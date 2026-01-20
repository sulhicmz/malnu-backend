<?php

namespace App\Http\Controllers\Api\Monetization;

use App\Http\Controllers\Api\BaseController;
use App\Models\Monetization\Invoice;
use App\Models\Monetization\Payment;
use App\Models\User;
use Hyperf\HttpServer\Contract\RequestInterface;
use Hyperf\HttpServer\Contract\ResponseInterface;
use Psr\Container\ContainerInterface;
use OpenApi\Annotations as OA;

/**
 * @OA\Tag(
 *     name="Payment",
 *     description="Payment management endpoints"
 * )
 */
class PaymentController extends BaseController
{
    private Payment $paymentModel;
    private Invoice $invoiceModel;
    private User $userModel;

    public function __construct(
        RequestInterface $request,
        ResponseInterface $response,
        ContainerInterface $container,
        Payment $paymentModel,
        Invoice $invoiceModel,
        User $userModel
    ) {
        parent::__construct($request, $response, $container);
        $this->paymentModel = $paymentModel;
        $this->invoiceModel = $invoiceModel;
        $this->userModel = $userModel;
    }

    /**
     * @OA\Get(
     *     path="/api/monetization/payments",
     *     tags={"Payment"},
     *     summary="List all payments",
     *     @OA\Parameter(
     *         name="student_id",
     *         in="query",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="invoice_id",
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
     *     @OA\Response(
     *         response=200,
     *         description="List of payments"
     *     )
     * )
     */
    public function index()
    {
        $query = $this->paymentModel->newQuery();

        $studentId = $this->request->input('student_id');
        if ($studentId) {
            $query->where('student_id', $studentId);
        }

        $invoiceId = $this->request->input('invoice_id');
        if ($invoiceId) {
            $query->where('invoice_id', $invoiceId);
        }

        $status = $this->request->input('status');
        if ($status) {
            $query->where('status', $status);
        }

        $page = (int)($this->request->input('page', 1));
        $perPage = (int)($this->request->input('per_page', 15));

        $payments = $query->with(['invoice', 'student'])
            ->orderBy('payment_date', 'desc')
            ->offset(($page - 1) * $perPage)
            ->limit($perPage)
            ->get();

        $total = $query->count();

        return $this->successResponse([
            'payments' => $payments,
            'pagination' => [
                'page' => $page,
                'per_page' => $perPage,
                'total' => $total,
                'total_pages' => (int)ceil($total / $perPage),
            ],
        ], 'Payments retrieved successfully');
    }

    /**
     * @OA\Get(
     *     path="/api/monetization/payments/{id}",
     *     tags={"Payment"},
     *     summary="Get payment by ID",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Payment details"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Payment not found"
     *     )
     * )
     */
    public function show(string $id)
    {
        $payment = $this->paymentModel->with(['invoice', 'student'])->find($id);

        if (!$payment) {
            return $this->errorResponse('Payment not found', 'PAYMENT_NOT_FOUND');
        }

        return $this->successResponse($payment, 'Payment retrieved successfully');
    }

    /**
     * @OA\Post(
     *     path="/api/monetization/payments",
     *     tags={"Payment"},
     *     summary="Create new payment",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"invoice_id", "amount", "payment_method", "payment_date"},
     *             @OA\Property(property="invoice_id", type="string", example="uuid-of-invoice"),
     *             @OA\Property(property="amount", type="number", format="float", example=100.50),
     *             @OA\Property(property="payment_method", type="string", example="cash"),
     *             @OA\Property(property="payment_reference", type="string", example="REF123456"),
     *             @OA\Property(property="payment_date", type="string", format="date", example="2026-01-20"),
     *             @OA\Property(property="notes", type="string", example="Payment notes")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Payment created successfully"
     *     )
     * )
     */
    public function store()
    {
        $data = $this->request->all();

        if (!$data['invoice_id'] ?? null) {
            return $this->errorResponse('Invoice ID is required', 'MISSING_INVOICE_ID');
        }

        if (!$data['amount'] ?? null) {
            return $this->errorResponse('Amount is required', 'MISSING_AMOUNT');
        }

        if (!$data['payment_method'] ?? null) {
            return $this->errorResponse('Payment method is required', 'MISSING_PAYMENT_METHOD');
        }

        $invoice = $this->invoiceModel->find($data['invoice_id']);
        if (!$invoice) {
            return $this->errorResponse('Invoice not found', 'INVOICE_NOT_FOUND');
        }

        if ($invoice->payment_status === 'paid') {
            return $this->errorResponse('Invoice is already fully paid', 'INVOICE_ALREADY_PAID');
        }

        $paymentData = [
            'invoice_id' => $data['invoice_id'],
            'student_id' => $invoice->student_id,
            'amount' => $data['amount'],
            'payment_method' => $data['payment_method'],
            'payment_reference' => $data['payment_reference'] ?? null,
            'payment_date' => $data['payment_date'] ?? date('Y-m-d'),
            'status' => 'completed',
            'transaction_id' => $data['transaction_id'] ?? null,
            'notes' => $data['notes'] ?? null,
        ];

        $payment = $this->paymentModel->create($paymentData);

        $totalPaid = $invoice->payments()->where('status', 'completed')->sum('amount');
        $updatedPaymentStatus = ($totalPaid >= $invoice->total_amount) ? 'paid' : 'partial';

        $invoice->update([
            'payment_status' => $updatedPaymentStatus,
        ]);

        return $this->successResponse($payment, 'Payment created successfully');
    }

    /**
     * @OA\Put(
     *     path="/api/monetization/payments/{id}",
     *     tags={"Payment"},
     *     summary="Update payment",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\RequestBody(
     *         required=false,
     *         @OA\JsonContent(
     *             @OA\Property(property="status", type="string", example="completed"),
     *             @OA\Property(property="notes", type="string", example="Updated notes")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Payment updated successfully"
     *     ),
     *     @OA\Response(
     *         response=404,
     *         description="Payment not found"
     *     )
     * )
     */
    public function update(string $id)
    {
        $payment = $this->paymentModel->find($id);
        if (!$payment) {
            return $this->errorResponse('Payment not found', 'PAYMENT_NOT_FOUND', null, 404);
        }

        $data = $this->request->only(['status', 'notes']);

        $payment->update($data);

        return $this->successResponse($payment, 'Payment updated successfully');
    }
}
