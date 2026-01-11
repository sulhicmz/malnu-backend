<?php

declare(strict_types=1);

namespace App\Models\Hostel;

use App\Models\Model;

class Room extends Model
{
    protected $table = 'rooms';

    protected $fillable = [
        'hostel_id',
        'room_number',
        'floor',
        'room_type',
        'capacity',
        'current_occupancy',
        'is_available',
        'amenities',
        'description',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'amenities' => 'array',
        'is_available' => 'boolean',
        'capacity' => 'integer',
        'current_occupancy' => 'integer',
        'created_by' => 'string',
        'updated_by' => 'string',
    ];

    public function hostel()
    {
        return $this->belongsTo(Hostel::class, 'hostel_id');
    }

    public function assignments()
    {
        return $this->hasMany(RoomAssignment::class, 'room_id');
    }

    public function activeAssignments()
    {
        return $this->hasMany(RoomAssignment::class, 'room_id')->where('status', 'active');
    }

    public function maintenanceRequests()
    {
        return $this->hasMany(MaintenanceRequest::class, 'room_id');
    }

    public function attendance()
    {
        return $this->hasMany(BoardingAttendance::class, 'room_id');
    }

    public function scopeAvailable($query)
    {
        return $query->where('is_available', true);
    }

    public function scopeHasCapacity($query)
    {
        return $query->whereRaw('current_occupancy < capacity');
    }

    public function scopeByType($query, $type)
    {
        return $query->where('room_type', $type);
    }

    public function getAvailableBedsAttribute()
    {
        return $this->capacity - $this->current_occupancy;
    }

    public function getIsFullAttribute()
    {
        return $this->current_occupancy >= $this->capacity;
    }
}
