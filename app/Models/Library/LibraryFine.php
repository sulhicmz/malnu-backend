<?php

declare(strict_types=1);

namespace App\Models\Library;

use App\Models\Model;
use App\Models\DigitalLibrary\BookLoan;

class LibraryFine extends Model
{
    protected $primaryKey = 'id';
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'patron_id',
        'loan_id',
        'fine_type',
        'amount',
        'amount_paid',
        'payment_status',
        'fine_date',
        'due_date',
        'payment_date',
        'description',
        'notes',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'amount_paid' => 'decimal:2',
        'fine_date' => 'date',
        'due_date' => 'date',
        'payment_date' => 'date',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function patron()
    {
        return $this->belongsTo(LibraryPatron::class, 'patron_id');
    }

    public function loan()
    {
        return $this->belongsTo(BookLoan::class, 'loan_id');
    }

    public function scopePending($query)
    {
        return $query->where('payment_status', 'pending');
    }

    public function scopePaid($query)
    {
        return $query->where('payment_status', 'paid');
    }

    public function scopeWaived($query)
    {
        return $query->where('payment_status', 'waived');
    }

    public function getRemainingBalanceAttribute(): float
    {
        return (float) $this->amount - $this->amount_paid;
    }

    public function isPaid(): bool
    {
        return $this->payment_status === 'paid' || $this->getRemainingBalanceAttribute() === 0;
    }

    public function isOverdue(): bool
    {
        return $this->due_date && $this->due_date->isPast() && !$this->isPaid();
    }
}
