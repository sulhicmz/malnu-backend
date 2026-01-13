<?php

declare(strict_types=1);

namespace App\Models\Hostel;

use App\Models\Model;
use App\Models\User;

class Hostel extends Model
{
    protected $table = 'hostels';

    protected $fillable = [
        'name',
        'code',
        'type',
        'gender',
        'capacity',
        'current_occupancy',
        'warden_name',
        'warden_contact',
        'address',
        'facilities',
        'is_active',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'facilities' => 'array',
        'is_active' => 'boolean',
        'capacity' => 'integer',
        'current_occupancy' => 'integer',
        'created_by' => 'string',
        'updated_by' => 'string',
    ];

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updatedBy()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function rooms()
    {
        return $this->hasMany(Room::class, 'hostel_id');
    }

    public function roomAssignments()
    {
        return $this->hasMany(RoomAssignment::class, 'hostel_id');
    }

    public function maintenanceRequests()
    {
        return $this->hasMany(MaintenanceRequest::class, 'hostel_id');
    }

    public function visitors()
    {
        return $this->hasMany(Visitor::class, 'hostel_id');
    }

    public function mealPlans()
    {
        return $this->hasMany(MealPlan::class, 'hostel_id');
    }

    public function attendance()
    {
        return $this->hasMany(BoardingAttendance::class, 'hostel_id');
    }

    public function healthRecords()
    {
        return $this->hasMany(HealthRecord::class, 'hostel_id');
    }

    public function incidents()
    {
        return $this->hasMany(Incident::class, 'hostel_id');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function getAvailableCapacityAttribute()
    {
        return $this->capacity - $this->current_occupancy;
    }

    public function getOccupancyPercentageAttribute()
    {
        return $this->capacity > 0 ? ($this->current_occupancy / $this->capacity) * 100 : 0;
    }
}
