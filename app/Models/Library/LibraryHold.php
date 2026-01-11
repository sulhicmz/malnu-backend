<?php

declare(strict_types=1);

namespace App\Models\Library;

use App\Models\Model;
use App\Models\DigitalLibrary\Book;

class LibraryHold extends Model
{
    protected $primaryKey = 'id';
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'book_id',
        'patron_id',
        'hold_type',
        'status',
        'request_date',
        'ready_date',
        'expiry_date',
        'fulfilled_date',
        'priority',
        'notes',
    ];

    protected $casts = [
        'request_date' => 'date',
        'ready_date' => 'date',
        'expiry_date' => 'date',
        'fulfilled_date' => 'date',
        'priority' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function book()
    {
        return $this->belongsTo(Book::class);
    }

    public function patron()
    {
        return $this->belongsTo(LibraryPatron::class, 'patron_id');
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeReady($query)
    {
        return $query->where('status', 'ready');
    }

    public function scopeHold($query)
    {
        return $query->where('hold_type', 'hold');
    }

    public function scopeRecall($query)
    {
        return $query->where('hold_type', 'recall');
    }

    public function scopeByPriority($query)
    {
        return $query->orderBy('priority', 'asc')->orderBy('request_date', 'asc');
    }

    public function isReady(): bool
    {
        return $this->status === 'ready';
    }

    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    public function isExpired(): bool
    {
        return $this->expiry_date && $this->expiry_date->isPast() && $this->status !== 'fulfilled';
    }
}
