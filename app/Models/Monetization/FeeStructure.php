<?php

declare(strict_types=1);

namespace App\Models\Monetization;

use App\Models\Model;

class FeeStructure extends Model
{
    protected $primaryKey = 'id';
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'fee_type_id',
        'name',
        'amount',
        'academic_year',
        'student_class',
        'student_type',
        'due_date',
        'is_recurring',
        'recurrence_frequency',
        'is_active',
        'created_by',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'due_date' => 'date',
        'is_recurring' => 'boolean',
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

    public function creator()
    {
        return $this->belongsTo(\App\Models\User::class, 'created_by');
    }
}
