<?php

declare(strict_types=1);

namespace App\Models\Hostel;

use App\Models\Model;
use App\Models\User;

class MaintenanceRequest extends Model
{
    protected $table = 'maintenance_requests';

    protected $fillable = [
        'hostel_id',
        'room_id',
        'reported_by',
        'type',
        'priority',
        'description',
        'status',
        'resolution_notes',
        'resolved_at',
        'resolved_by',
    ];

    protected $casts = [
        'resolved_at' => 'date',
        'resolved_by' => 'string',
    ];

    public function hostel()
    {
        return $this->belongsTo(Hostel::class, 'hostel_id');
    }

    public function room()
    {
        return $this->belongsTo(Room::class, 'room_id');
    }

    public function reportedBy()
    {
        return $this->belongsTo(User::class, 'reported_by');
    }

    public function resolvedBy()
    {
        return $this->belongsTo(User::class, 'resolved_by');
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeInProgress($query)
    {
        return $query->where('status', 'in_progress');
    }

    public function scopeResolved($query)
    {
        return $query->where('status', 'resolved');
    }

    public function scopeByPriority($query, $priority)
    {
        return $query->where('priority', $priority);
    }

    public function scopeByHostel($query, $hostelId)
    {
        return $query->where('hostel_id', $hostelId);
    }

    public function scopeHighPriority($query)
    {
        return $query->whereIn('priority', ['high', 'critical']);
    }

    public function getIsResolvedAttribute()
    {
        return $this->status === 'resolved';
    }

    public function getResolutionTimeAttribute()
    {
        if (!$this->resolved_at || !$this->created_at) {
            return null;
        }
        return $this->created_at->diffInDays($this->resolved_at);
    }
}
