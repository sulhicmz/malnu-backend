<?php

declare(strict_types=1);

namespace App\Models\ParentPortal;

use App\Models\Model;
use App\Models\SchoolManagement\Student;
use App\Models\User;

class ParentConference extends Model
{
    protected $primaryKey = 'id';
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'parent_id',
        'teacher_id',
        'student_id',
        'scheduled_date',
        'duration_minutes',
        'status',
        'notes',
        'teacher_notes',
        'parent_notes',
        'reminders_sent',
    ];

    protected $casts = [
        'scheduled_date' => 'datetime',
        'duration_minutes' => 'integer',
        'reminders_sent' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function parent()
    {
        return $this->belongsTo(User::class, 'parent_id');
    }

    public function teacher()
    {
        return $this->belongsTo(User::class, 'teacher_id');
    }

    public function student()
    {
        return $this->belongsTo(Student::class, 'student_id');
    }
}
