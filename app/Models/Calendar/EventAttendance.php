<?php

declare(strict_types=1);

namespace App\Models\Calendar;

use App\Models\User;
use App\Models\Model;

class EventAttendance extends Model
{
    protected $table = 'event_attendance';

    protected $fillable = [
        'event_id',
        'user_id',
        'check_in_time',
        'check_out_time',
        'status',
        'notes',
        'additional_data',
    ];

    protected $casts = [
        'check_in_time' => 'datetime',
        'check_out_time' => 'datetime',
        'event_id' => 'string',
        'user_id' => 'string',
        'additional_data' => 'array',
    ];

    public function event()
    {
        return $this->belongsTo(CalendarEvent::class, 'event_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function scopeAttended($query)
    {
        return $query->where('status', 'attended');
    }

    public function scopeAbsent($query)
    {
        return $query->where('status', 'absent');
    }

    public function scopeByEvent($query, $eventId)
    {
        return $query->where('event_id', $eventId);
    }

    public function scopeByUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }
}
