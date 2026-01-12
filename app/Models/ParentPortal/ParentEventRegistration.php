<?php

declare(strict_types=1);

namespace App\Models\ParentPortal;

use App\Models\Calendar\CalendarEvent;
use App\Models\Model;
use App\Models\SchoolManagement\Student;
use App\Models\User;

class ParentEventRegistration extends Model
{
    protected $primaryKey = 'id';
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'parent_id',
        'student_id',
        'event_id',
        'status',
        'number_of_attendees',
        'additional_info',
        'registered_at',
    ];

    protected $casts = [
        'number_of_attendees' => 'integer',
        'additional_info' => 'array',
        'registered_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function parent()
    {
        return $this->belongsTo(User::class, 'parent_id');
    }

    public function student()
    {
        return $this->belongsTo(Student::class, 'student_id');
    }

    public function event()
    {
        return $this->belongsTo(CalendarEvent::class, 'event_id');
    }
}
