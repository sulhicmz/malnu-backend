<?php

declare(strict_types=1);

namespace App\Models\FinancialManagement;

use App\Models\Model;

class Payment extends Model
{
    protected $fillable = [
        'invoice_id',
        'student_id',
        'amount',
        'payment_method',
        'reference_number',
        'payment_date',
        'notes',
        'status',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'payment_date' => 'date',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function invoice()
    {
        return $this->belongsTo(Invoice::class);
    }

    public function student()
    {
        return $this->belongsTo(\App\Models\User::class);
    }
}
