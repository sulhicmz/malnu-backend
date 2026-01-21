<?php

declare(strict_types = 1);

namespace App\Models\SchoolManagement;

use App\Models\Model;

class HostelRoom extends Model
{
    protected $primaryKey = 'id';
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'hostel_id',
        'room_number',
        'capacity',
        'current_occupancy',
        'room_type',
        'amenities',
        'floor',
        'status',
    ];

    protected $casts = [
        'capacity' => 'integer',
        'current_occupancy' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function hostel()
    {
        return $this->belongsTo(Hostel::class);
    }

    public function allocations()
    {
        return $this->hasMany(HostelAllocation::class);
    }

    public function scopeAvailable($query)
    {
        return $query->where('status', 'available');
    }

    public function scopeOccupied($query)
    {
        return $query->where('status', 'occupied');
    }

    public function scopeMaintenance($query)
    {
        return $query->where('status', 'maintenance');
    }

    public function isFull()
    {
        return $this->current_occupancy >= $this->capacity;
    }

    public function getAvailableSlots()
    {
        return max(0, $this->capacity - $this->current_occupancy);
    }
}
