<?php

declare(strict_types = 1);

namespace App\Models\FeeManagement;

use App\Models\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class FeeStructure extends Model
{
    protected $primaryKey = 'id';
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'fee_type_id',
        'grade_level',
        'academic_year',
        'amount',
        'payment_schedule',
        'due_date',
        'late_fee_percentage',
        'is_active',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'late_fee_percentage' => 'decimal:2',
        'due_date' => 'date',
        'is_active' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function feeType(): BelongsTo
    {
        return $this->belongsTo(FeeType::class);
    }

    public function invoices(): HasMany
    {
        return $this->hasMany(FeeInvoice::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeForGrade($query, string $grade)
    {
        return $query->where('grade_level', $grade);
    }

    public function scopeForAcademicYear($query, string $year)
    {
        return $query->where('academic_year', $year);
    }

    public function calculateLateFee(int $daysLate): float
    {
        if ($daysLate <= 0) {
            return 0;
        }
        return (float) $this->amount * ($this->late_fee_percentage / 100) * $daysLate;
    }
}
