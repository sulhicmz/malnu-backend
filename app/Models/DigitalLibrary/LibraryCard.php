<?php

declare(strict_types=1);

namespace App\Models\DigitalLibrary;

use App\Models\Model;
use App\Models\User;

class LibraryCard extends Model
{
    protected $primaryKey = 'id';
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'user_id',
        'card_number',
        'issue_date',
        'expiry_date',
        'is_active',
        'status',
    ];

    protected $casts = [
        'issue_date' => 'date',
        'expiry_date' => 'date',
        'is_active' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function bookLoans()
    {
        return $this->hasMany(BookLoan::class, 'library_card_id');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true)
            ->where('status', 'active')
            ->where(function ($q) {
                $q->whereNull('expiry_date')
                    ->orWhere('expiry_date', '>', now());
            });
    }

    public function scopeExpired($query)
    {
        return $query->where('expiry_date', '<', now());
    }

    public function isExpired(): bool
    {
        return $this->expiry_date && $this->expiry_date < now();
    }

    public function isActive(): bool
    {
        return $this->is_active && !$this->isExpired();
    }
}
