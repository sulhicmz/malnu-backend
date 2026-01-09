<?php

declare(strict_types = 1);

namespace App\Models\FeeManagement;

use App\Models\Model;
use App\Models\SchoolManagement\Student;
use App\Models\User;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FeeWaiver extends Model
{
    protected $primaryKey = 'id';
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'invoice_id',
        'student_id',
        'waiver_type',
        'waiver_code',
        'discount_percentage',
        'discount_amount',
        'reason',
        'valid_from',
        'valid_until',
        'status',
        'approved_by',
    ];

    protected $casts = [
        'discount_percentage' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'valid_from' => 'date',
        'valid_until' => 'date',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(FeeInvoice::class);
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active')
                     ->where('valid_from', '<=', now())
                     ->where(function ($q) {
                         $q->where('valid_until', '>=', now())
                           ->orWhereNull('valid_until');
                     });
    }

    public function scopeForStudent($query, string $studentId)
    {
        return $query->where('student_id', $studentId);
    }

    public function isValid(): bool
    {
        return $this->status === 'active'
            && $this->valid_from <= now()
            && ($this->valid_until === null || $this->valid_until >= now());
    }

    public function calculateDiscount(float $amount): float
    {
        if (!$this->isValid()) {
            return 0;
        }

        if ($this->discount_percentage > 0) {
            return $amount * ($this->discount_percentage / 100);
        }

        return $this->discount_amount;
    }
}
