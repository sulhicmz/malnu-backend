<?php

declare(strict_types=1);

namespace App\Models\Monetization;

use App\Models\Model;

class Expense extends Model
{
    protected $primaryKey = 'id';
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'category',
        'amount',
        'expense_date',
        'description',
        'vendor',
        'payment_method',
        'reference_number',
        'approval_status',
        'approved_by',
        'created_by',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'expense_date' => 'date',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function approver()
    {
        return $this->belongsTo(\App\Models\User::class, 'approved_by');
    }

    public function creator()
    {
        return $this->belongsTo(\App\Models\User::class, 'created_by');
    }
}
