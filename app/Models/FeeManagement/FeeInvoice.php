<?php

declare(strict_types = 1);

namespace App\Models\FeeManagement;

use App\Models\Model;
use App\Models\SchoolManagement\Student;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class FeeInvoice extends Model
{
    protected $primaryKey = 'id';
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'student_id',
        'fee_structure_id',
        'invoice_number',
        'issue_date',
        'due_date',
        'subtotal',
        'tax',
        'discount',
        'late_fee',
        'total_amount',
        'paid_amount',
        'balance_amount',
        'status',
        'notes',
    ];

    protected $casts = [
        'subtotal' => 'decimal:2',
        'tax' => 'decimal:2',
        'discount' => 'decimal:2',
        'late_fee' => 'decimal:2',
        'total_amount' => 'decimal:2',
        'paid_amount' => 'decimal:2',
        'balance_amount' => 'decimal:2',
        'issue_date' => 'date',
        'due_date' => 'date',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function feeStructure(): BelongsTo
    {
        return $this->belongsTo(FeeStructure::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(FeePayment::class);
    }

    public function waivers(): HasMany
    {
        return $this->hasMany(FeeWaiver::class);
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopePartiallyPaid($query)
    {
        return $query->where('status', 'partially_paid');
    }

    public function scopePaid($query)
    {
        return $query->where('status', 'paid');
    }

    public function scopeOverdue($query)
    {
        return $query->where('status', '!=', 'paid')
                     ->where('due_date', '<', now());
    }

    public function scopeByStudent($query, string $studentId)
    {
        return $query->where('student_id', $studentId);
    }

    public function isOverdue(): bool
    {
        return $this->status !== 'paid' && $this->due_date < now();
    }

    public function updateBalance(): void
    {
        $this->paid_amount = $this->payments()->where('status', 'completed')->sum('amount');
        $this->balance_amount = $this->total_amount - $this->paid_amount;

        if ($this->balance_amount <= 0) {
            $this->status = 'paid';
        } elseif ($this->paid_amount > 0) {
            $this->status = 'partially_paid';
        } else {
            $this->status = 'pending';
        }

        $this->save();
    }
}
