<?php

declare(strict_types=1);

namespace App\Models\Calendar;

use App\Models\User;
use App\Models\Model;
use Hyperf\Database\Model\SoftDeletes;

class CalendarEvent extends Model
{
    use SoftDeletes;

    protected string $primaryKey = 'id';
    protected string $keyType = 'string';
    public bool $incrementing = false;

    protected $table = 'calendar_events';

    protected $fillable = [
        'calendar_id',
        'title',
        'description',
        'start_date',
        'end_date',
        'location',
        'category',
        'priority',
        'is_all_day',
        'is_recurring',
        'recurrence_pattern',
        'recurrence_end_date',
        'max_attendees',
        'requires_registration',
        'registration_deadline',
        'metadata',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'recurrence_pattern' => 'array',
        'metadata' => 'array',
        'start_date' => 'datetime',
        'end_date' => 'datetime',
        'recurrence_end_date' => 'datetime',
        'registration_deadline' => 'datetime',
        'is_all_day' => 'boolean',
        'is_recurring' => 'boolean',
        'requires_registration' => 'boolean',
        'calendar_id' => 'uuid',
        'created_by' => 'uuid',
        'updated_by' => 'uuid',
    ];

    // Relationships
    public function calendar()
    {
        return $this->belongsTo(Calendar::class, 'calendar_id');
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updatedBy()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function registrations()
    {
        return $this->hasMany(CalendarEventRegistration::class, 'event_id');
    }
}