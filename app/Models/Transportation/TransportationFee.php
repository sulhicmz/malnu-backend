<?php

declare(strict_types=1);

namespace App\Models\Transportation;

use App\Models\Model;
use App\Models\Transportation\TransportationRegistration;

class TransportationFee extends Model
{
    protected $primaryKey = 'id';
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'registration_id',
        'fee_type',
        'amount',
        'currency',
        'due_date',
        'paid_date',
        'payment_status',
        'payment_method',
        'transaction_reference',
        'description',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'due_date' => 'date',
        'paid_date' => 'date',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function registration()
    {
        return $this->belongsTo(TransportationRegistration::class);
    }

    public function scopePaid($query)
    {
        return $query->where('payment_status', 'paid');
    }

    public function scopePending($query)
    {
        return $query->where('payment_status', 'pending');
    }

    public function scopeOverdue($query)
    {
        return $query->where('payment_status', 'overdue');
    }
}
