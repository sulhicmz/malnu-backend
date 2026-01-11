<?php

declare(strict_types=1);

namespace App\Models\Alumni;

use App\Models\Model;

class AlumniEventRegistration extends Model
{
    protected $table = 'alumni_event_registrations';

    protected $fillable = [
        'event_id',
        'alumni_id',
        'name',
        'email',
        'phone',
        'guests',
        'is_attending',
        'dietary_requirements',
        'special_requests',
        'registration_date',
        'check_in_status',
        'check_in_time',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'is_attending' => 'boolean',
        'check_in_status' => 'boolean',
        'guests' => 'integer',
        'registration_date' => 'datetime',
        'check_in_time' => 'datetime',
        'created_by' => 'string',
        'updated_by' => 'string',
    ];

    public function event()
    {
        return $this->belongsTo(AlumniEvent::class, 'event_id');
    }

    public function alumni()
    {
        return $this->belongsTo(Alumni::class, 'alumni_id');
    }

    public function scopeAttending($query)
    {
        return $query->where('is_attending', true);
    }

    public function scopeCheckedIn($query)
    {
        return $query->where('check_in_status', true);
    }

    public function scopeByEvent($query, $eventId)
    {
        return $query->where('event_id', $eventId);
    }

    public function scopeByAlumni($query, $alumniId)
    {
        return $query->where('alumni_id', $alumniId);
    }

    public function markAsCheckedIn()
    {
        $this->update([
            'check_in_status' => true,
            'check_in_time' => now(),
        ]);
    }

    public function getTotalAttendeesAttribute()
    {
        return $this->is_attending ? 1 + $this->guests : 0;
    }
}
