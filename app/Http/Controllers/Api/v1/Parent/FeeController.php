<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\v1\Parent;

use App\Http\Controllers\Api\v1\ApiController;
use App\Models\Monetization\Transaction;
use Hypervel\Http\Request;

class FeeController extends ApiController
{
    public function index(Request $request)
    {
        $user = $request->user();
        $parent = $user->parent;
        
        if (!$parent) {
            return $this->errorResponse('Parent profile not found', 404);
        }

        // Get related students
        $students = $parent->students;

        // Get transactions for all related students
        $transactions = Transaction::whereIn('user_id', $students->pluck('user_id'))
            ->with(['user'])
            ->orderBy('created_at', 'desc')
            ->get();

        // Calculate fee summary
        $totalPaid = $transactions->where('status', 'completed')->sum('amount');
        $totalPending = $transactions->where('status', 'pending')->sum('amount');

        return $this->successResponse([
            'transactions' => $transactions,
            'summary' => [
                'total_paid' => $totalPaid,
                'total_pending' => $totalPending,
                'balance' => $totalPending, // Simplified for demo
            ],
        ]);
    }
}