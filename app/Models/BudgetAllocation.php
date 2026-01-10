<?php

namespace App\Models;

use Hyperf\DbConnection\Model\Model;

class BudgetAllocation extends Model{
    protected $table = 'budget_allocations';

    protected $fillable = [
        'budget_code',
        'name',
        'category',
        'department',
        'academic_year',
        'allocated_amount',
        'spent_amount',
        'remaining_amount',
        'start_date',
        'end_date',
        'status',
        'manager_id',
        'notes',
    ];

    protected $casts = [
        'allocated_amount' => 'decimal:2',
        'spent_amount' => 'decimal:2',
        'remaining_amount' => 'decimal:2',
        'start_date' => 'date',
        'end_date' => 'date',
    ];

    public function manager()
    {
        return $this->belongsTo(Staff::class, 'manager_id');
    }

    public function expenses()
    {
        return $this->hasMany(Expense::class, 'budget_allocation_id');
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeExpiringSoon($query)
    {
        return $query->where('status', 'active')
            ->where('end_date', '<=', now()->addDays(30));
    }
}
