<?php

declare(strict_types=1);

namespace App\Models\Alumni;

use App\Models\Model;

class AlumniEvent extends Model
{
    protected $table = 'alumni_events';

    protected $fillable = [
        'name',
        'slug',
        'description',
        'event_type',
        'event_date',
        'end_date',
        'location',
        'virtual_link',
        'is_virtual',
        'max_capacity',
        'current_attendees',
        'status',
        'image_url',
        'organizer_name',
        'contact_email',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'is_virtual' => 'boolean',
        'event_date' => 'datetime',
        'end_date' => 'datetime',
        'max_capacity' => 'integer',
        'current_attendees' => 'integer',
        'created_by' => 'string',
        'updated_by' => 'string',
    ];

    public function registrations()
    {
        return $this->hasMany(AlumniEventRegistration::class, 'event_id');
    }

    public function scopeUpcoming($query)
    {
        return $query->where('event_date', '>=', now())->orderBy('event_date');
    }

    public function scopePast($query)
    {
        return $query->where('event_date', '<', now())->orderBy('event_date', 'desc');
    }

    public function scopeByType($query, $type)
    {
        return $query->where('event_type', $type);
    }

    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    public function getCapacityStatusAttribute()
    {
        if (!$this->max_capacity) {
            return 'unlimited';
        }
        $percentage = ($this->current_attendees / $this->max_capacity) * 100;
        if ($percentage >= 100) {
            return 'full';
        } elseif ($percentage >= 80) {
            return 'almost_full';
        }
        return 'available';
    }

    public function isFullyBooked()
    {
        return $this->max_capacity && $this->current_attendees >= $this->max_capacity;
    }

    public function hasCapacity()
    {
        return !$this->max_capacity || $this->current_attendees < $this->max_capacity;
    }

    public function getAvailableSlotsAttribute()
    {
        if (!$this->max_capacity) {
            return null;
        }
        return max(0, $this->max_capacity - $this->current_attendees);
    }
}
