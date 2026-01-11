<?php

declare(strict_types=1);

namespace App\Models\Library;

use App\Models\Model;
use App\Models\User;

class LibrarySpaceBooking extends Model
{
    protected $primaryKey = 'id';
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'space_id',
        'user_id',
        'start_time',
        'end_time',
        'status',
        'attendees',
        'purpose',
        'notes',
    ];

    protected $casts = [
        'start_time' => 'datetime',
        'end_time' => 'datetime',
        'attendees' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function space()
    {
        return $this->belongsTo(LibrarySpace::class, 'space_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function scopeConfirmed($query)
    {
        return $query->where('status', 'confirmed');
    }

    public function scopeCancelled($query)
    {
        return $query->where('status', 'cancelled');
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    public function scopeUpcoming($query)
    {
        return $query->where('start_time', '>', now());
    }

    public function scopePast($query)
    {
        return $query->where('end_time', '<', now());
    }

    public function scopeByUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    public function scopeByDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('start_time', [$startDate, $endDate]);
    }

    public function isActive(): bool
    {
        return $this->status === 'confirmed' &&
            now()->between($this->start_time, $this->end_time);
    }

    public function isUpcoming(): bool
    {
        return $this->status === 'confirmed' && $this->start_time->isFuture();
    }

    public function isPast(): bool
    {
        return $this->end_time->isPast();
    }
}
