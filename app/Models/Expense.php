<?php

namespace App\Models;

use Hyperf\DbConnection\Model\Model;

class Expense extends Model
{
    protected $table = 'expenses';

    protected $fillable = [
        'budget_allocation_id',
        'description',
        'amount',
        'expense_date',
        'category',
        'payment_method',
        'vendor',
        'requester_id',
        'approver_id',
        'status',
        'receipt_path',
        'justification',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'expense_date' => 'date',
    ];

    public function budgetAllocation()
    {
        return $this->belongsTo(BudgetAllocation::class, 'budget_allocation_id');
    }

    public function requester()
    {
        return $this->belongsTo(Staff::class, 'requester_id');
    }

    public function approver()
    {
        return $this->belongsTo(Staff::class, 'approver_id');
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    public function scopeRejected($query)
    {
        return $query->where('status', 'rejected');
    }
}
