<?php

declare(strict_types=1);

namespace App\Models\FinancialManagement;

use App\Models\Model;

class FeeStructure extends Model
{
    protected $fillable = [
        'fee_type_id',
        'name',
        'amount',
        'frequency',
        'student_type',
        'student_class_id',
        'academic_year',
        'due_date',
        'is_active',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'due_date' => 'date',
        'is_active' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function feeType()
    {
        return $this->belongsTo(FeeType::class);
    }

    public function invoices()
    {
        return $this->hasMany(Invoice::class);
    }
}
