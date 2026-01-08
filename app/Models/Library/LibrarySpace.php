<?php

declare(strict_types=1);

namespace App\Models\Library;

use App\Models\Model;
use App\Models\User;

class LibrarySpace extends Model
{
    protected $primaryKey = 'id';
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'space_name',
        'space_type',
        'capacity',
        'availability',
        'equipment',
        'amenities',
        'rules',
    ];

    protected $casts = [
        'capacity' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function bookings()
    {
        return $this->hasMany(LibrarySpaceBooking::class, 'space_id');
    }

    public function scopeAvailable($query)
    {
        return $query->where('availability', 'available');
    }

    public function scopeBooked($query)
    {
        return $query->where('availability', 'booked');
    }

    public function scopeMaintenance($query)
    {
        return $query->where('availability', 'maintenance');
    }

    public function scopeStudyRoom($query)
    {
        return $query->where('space_type', 'study_room');
    }

    public function scopeComputerLab($query)
    {
        return $query->where('space_type', 'computer_lab');
    }

    public function scopeMeetingRoom($query)
    {
        return $query->where('space_type', 'meeting_room');
    }

    public function scopeCollaborativeArea($query)
    {
        return $query->where('space_type', 'collaborative_area');
    }

    public function isAvailable(): bool
    {
        return $this->availability === 'available';
    }

    public function isUnderMaintenance(): bool
    {
        return $this->availability === 'maintenance';
    }
}
