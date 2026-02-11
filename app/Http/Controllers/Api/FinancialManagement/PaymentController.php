<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\FinancialManagement;

use App\Http\Controllers\Api\BaseController;
use App\Models\FinancialManagement\Invoice;
use App\Models\FinancialManagement\Payment;
use Hyperf\DbConnection\Db;
use Hyperf\HttpServer\Contract\RequestInterface;
use Hyperf\HttpServer\Contract\ResponseInterface;
use Psr\Container\ContainerInterface;
use OpenApi\Annotations as OA;

class PaymentController extends BaseController
{
    protected string $resourceName = 'Payment';

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
            $query = Payment::query();

            $page = (int) $this->request->query('page', 1);
            $limit = (int) $this->request->query('limit', 15);

            if ($invoiceId = $this->request->query('invoice_id')) {
                $query->where('invoice_id', $invoiceId);
            }

            if ($studentId = $this->request->query('student_id')) {
                $query->where('student_id', $studentId);
            }

            if ($status = $this->request->query('status')) {
                $query->where('status', $status);
            }

            $results = $query->with(['invoice', 'student'])
                ->orderBy('payment_date', 'desc')
                ->paginate($limit, ['*'], 'page', $page);

            return $this->successResponse($results, 'Payments retrieved successfully');
        } catch (\Exception $e) {
            return $this->serverErrorResponse($e->getMessage());
        }
    }

    public function show(string $id)
    {
        try {
            $payment = Payment::with(['invoice', 'student'])
                ->find($id);

            if (! $payment) {
                return $this->notFoundResponse('Payment not found');
            }

            return $this->successResponse($payment, 'Payment retrieved successfully');
        } catch (\Exception $e) {
            return $this->serverErrorResponse($e->getMessage());
        }
    }

    public function store()
    {
        try {
            $data = $this->request->all();

            $errors = $this->validatePaymentData($data);

            if (! empty($errors)) {
                return $this->validationErrorResponse($errors);
            }

            $invoice = Invoice::find($data['invoice_id']);

            if (! $invoice) {
                return $this->errorResponse('Invoice not found', 'INVOICE_NOT_FOUND', 404);
            }

            if ($invoice->paid_amount + $data['amount'] > $invoice->total_amount) {
                return $this->errorResponse('Payment amount exceeds invoice balance', 'PAYMENT_EXCEEDS_BALANCE', 400);
            }

            Db::beginTransaction();

            $payment = Payment::create([
                'invoice_id' => $data['invoice_id'],
                'student_id' => $invoice->student_id,
                'amount' => $data['amount'],
                'payment_method' => $data['payment_method'],
                'reference_number' => $data['reference_number'] ?? null,
                'payment_date' => $data['payment_date'] ?? date('Y-m-d'),
                'notes' => $data['notes'] ?? null,
                'status' => 'completed',
            ]);

            $invoice->paid_amount += $data['amount'];

            if ($invoice->paid_amount >= $invoice->total_amount) {
                $invoice->status = 'paid';
                $invoice->paid_date = $data['payment_date'] ?? date('Y-m-d');
            } elseif ($invoice->paid_amount > 0) {
                $invoice->status = 'partial';
            }

            $invoice->save();

            Db::commit();

            return $this->successResponse($payment->load(['invoice', 'student']), 'Payment recorded successfully', 201);
        } catch (\Exception $e) {
            Db::rollBack();
            return $this->serverErrorResponse($e->getMessage());
        }
    }

    public function update(string $id)
    {
        try {
            $payment = Payment::find($id);

            if (! $payment) {
                return $this->notFoundResponse('Payment not found');
            }

            if ($payment->status === 'completed') {
                return $this->errorResponse('Cannot update completed payment', 'PAYMENT_COMPLETED', 400);
            }

            $data = $this->request->all();

            $errors = $this->validatePaymentData($data, true);

            if (! empty($errors)) {
                return $this->validationErrorResponse($errors);
            }

            $payment->update([
                'notes' => $data['notes'] ?? $payment->notes,
                'status' => $data['status'] ?? $payment->status,
            ]);

            return $this->successResponse($payment, 'Payment updated successfully');
        } catch (\Exception $e) {
            return $this->serverErrorResponse($e->getMessage());
        }
    }

    protected function validatePaymentData(array $data, bool $isUpdate = false): array
    {
        $errors = [];

        if (! $isUpdate && empty($data['invoice_id'])) {
            $errors[] = 'Invoice ID is required';
        }

        if (! $isUpdate && empty($data['amount'])) {
            $errors[] = 'Amount is required';
        }

        if (isset($data['amount']) && $data['amount'] <= 0) {
            $errors[] = 'Amount must be greater than zero';
        }

        if (! $isUpdate && !empty($data['payment_method'])) {
            $validMethods = ['cash', 'bank_transfer', 'card', 'e_wallet', 'check'];
            if (!in_array($data['payment_method'], $validMethods)) {
                $errors[] = 'Invalid payment method';
            }
        }

        return $errors;
    }
}
