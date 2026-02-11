<?php

declare(strict_types=1);

namespace App\Models\Calendar;

use App\Models\Model;
use App\Models\User;

class ResourceBooking extends Model
{
    protected $table = 'resource_bookings';

    protected $fillable = [
        'resource_type',
        'resource_id',
        'event_id',
        'booked_by',
        'start_time',
        'end_time',
        'purpose',
        'status',
        'booking_data',
    ];

    protected $casts = [
        'booking_data' => 'array',
        'start_time' => 'datetime',
        'end_time' => 'datetime',
        'event_id' => 'string',
        'booked_by' => 'string',
    ];

    // Relationships
    public function event()
    {
        return $this->belongsTo(CalendarEvent::class, 'event_id');
    }

    public function bookedBy()
    {
        return $this->belongsTo(User::class, 'booked_by');
    }
}
