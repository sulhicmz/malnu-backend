<?php

declare(strict_types=1);

namespace App\Models\DigitalLibrary;

use App\Models\Model;

class LoanPolicy extends Model
{
    protected $primaryKey = 'id';
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'name',
        'user_type',
        'max_books',
        'loan_duration_days',
        'renewal_limit',
        'fine_per_day',
        'grace_period_days',
        'is_active',
    ];

    protected $casts = [
        'max_books' => 'integer',
        'loan_duration_days' => 'integer',
        'renewal_limit' => 'integer',
        'fine_per_day' => 'decimal:2',
        'grace_period_days' => 'integer',
        'is_active' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeByUserType($query, $userType)
    {
        return $query->where('user_type', $userType);
    }

    public function isRenewalAllowed(int $renewalCount): bool
    {
        return $renewalCount < $this->renewal_limit;
    }

    public function calculateFine(int $overdueDays): float
    {
        $gracePeriodDays = $this->grace_period_days ?? 0;
        $chargeableDays = max(0, $overdueDays - $gracePeriodDays);
        
        return round($chargeableDays * $this->fine_per_day, 2);
    }
}
