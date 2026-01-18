<?php

declare(strict_types=1);

namespace App\Models\DigitalLibrary;

use App\Models\Model;
use App\Models\User;

class BookHold extends Model
{
    protected $primaryKey = 'id';
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'book_id',
        'patron_id',
        'hold_date',
        'expiry_date',
        'is_ready',
        'is_cancelled',
        'status',
    ];

    protected $casts = [
        'hold_date' => 'date',
        'expiry_date' => 'date',
        'is_ready' => 'boolean',
        'is_cancelled' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function book()
    {
        return $this->belongsTo(Book::class);
    }

    public function patron()
    {
        return $this->belongsTo(User::class, 'patron_id');
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active')
            ->where('is_cancelled', false)
            ->where(function ($q) {
                $q->whereNull('expiry_date')
                    ->orWhere('expiry_date', '>=', now());
            });
    }

    public function scopeReady($query)
    {
        return $query->where('is_ready', true)->where('status', 'active');
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending')
            ->where('is_cancelled', false)
            ->where(function ($q) {
                $q->whereNull('expiry_date')
                    ->orWhere('expiry_date', '>=', now());
            });
    }
}
