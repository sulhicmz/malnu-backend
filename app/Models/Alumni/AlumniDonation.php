<?php

declare(strict_types=1);

namespace App\Models\Alumni;

use App\Models\Model;

class AlumniDonation extends Model
{
    protected $table = 'alumni_donations';

    protected $fillable = [
        'alumni_id',
        'donor_name',
        'email',
        'phone',
        'amount',
        'currency',
        'donation_type',
        'campaign',
        'is_recurring',
        'recurring_frequency',
        'donation_date',
        'payment_method',
        'transaction_id',
        'is_anonymous',
        'message',
        'status',
        'receipt_details',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'is_recurring' => 'boolean',
        'is_anonymous' => 'boolean',
        'donation_date' => 'date',
        'receipt_details' => 'array',
        'created_by' => 'string',
        'updated_by' => 'string',
    ];

    public function alumni()
    {
        return $this->belongsTo(Alumni::class, 'alumni_id');
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeRecurring($query)
    {
        return $query->where('is_recurring', true);
    }

    public function scopeByCampaign($query, $campaign)
    {
        return $query->where('campaign', $campaign);
    }

    public function scopeByType($query, $type)
    {
        return $query->where('donation_type', $type);
    }

    public function getDonorNameAttribute()
    {
        if ($this->is_anonymous) {
            return 'Anonymous';
        }
        return $this->attributes['donor_name'] ?? ($this->alumni ? $this->alumni->full_name : null);
    }
}
