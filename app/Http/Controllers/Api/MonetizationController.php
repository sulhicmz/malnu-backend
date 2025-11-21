<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\ApiController;
use App\Models\Monetization\Transaction;
use Hypervel\Http\Request;
use Hypervel\Http\Response;

class MonetizationController extends ApiController
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): array
    {
        try {
            $transactions = Transaction::with(['user', 'items'])->paginate(
                $request->get('per_page', 15)
            );

            return $this->success($transactions, 'Transactions retrieved successfully');
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
                'user_id' => 'required|exists:users,id',
                'transaction_type' => 'required|in:payment,refund,fee,subscription',
                'amount' => 'required|numeric|min:0',
                'currency' => 'required|string|max:3',
                'status' => 'required|in:pending,paid,failed,cancelled,refunded',
                'payment_method' => 'required|in:cash,bank_transfer,credit_card,debit_card,online',
                'description' => 'nullable|string',
                'reference_number' => 'nullable|string|unique:transactions,reference_number',
                'due_date' => 'nullable|date',
                'payment_date' => 'nullable|date',
                'notes' => 'nullable|string',
            ]);

            $transaction = Transaction::create($validated);

            return $this->success($transaction, 'Transaction created successfully', 201);
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
            $transaction = Transaction::with(['user', 'items'])->findOrFail($id);

            return $this->success($transaction, 'Transaction retrieved successfully');
        } catch (\Exception $e) {
            return $this->error('Transaction not found', 404);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, int $id): array
    {
        try {
            $transaction = Transaction::findOrFail($id);

            $validated = $request->validate([
                'user_id' => 'sometimes|required|exists:users,id',
                'transaction_type' => 'sometimes|required|in:payment,refund,fee,subscription',
                'amount' => 'sometimes|required|numeric|min:0',
                'currency' => 'sometimes|required|string|max:3',
                'status' => 'sometimes|required|in:pending,paid,failed,cancelled,refunded',
                'payment_method' => 'sometimes|required|in:cash,bank_transfer,credit_card,debit_card,online',
                'description' => 'nullable|string',
                'reference_number' => 'nullable|string|unique:transactions,reference_number,' . $id,
                'due_date' => 'nullable|date',
                'payment_date' => 'nullable|date',
                'notes' => 'nullable|string',
            ]);

            $transaction->update($validated);

            return $this->success($transaction, 'Transaction updated successfully');
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
            $transaction = Transaction::findOrFail($id);
            $transaction->delete();

            return $this->success(null, 'Transaction deleted successfully');
        } catch (\Exception $e) {
            return $this->error('Transaction not found or could not be deleted', 404);
        }
    }
}