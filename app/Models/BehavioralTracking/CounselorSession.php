<?php

declare(strict_types=1);

namespace App\Models\BehavioralTracking;

use App\Models\Model;
use App\Models\SchoolManagement\Student;
use App\Models\User;

class CounselorSession extends Model
{
    protected $primaryKey = 'id';
    protected $keyType    = 'string';
    public $incrementing  = false;

    protected $fillable = [
        'student_id',
        'counselor_id',
        'session_date',
        'duration_minutes',
        'session_type',
        'session_notes',
        'observations',
        'follow_up_required',
        'follow_up_date',
        'is_private',
    ];

    protected $casts = [
        'session_date'     => 'datetime',
        'follow_up_date'    => 'datetime',
        'is_private'        => 'boolean',
        'created_at'        => 'datetime',
        'updated_at'        => 'datetime',
    ];

    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function counselor()
    {
        return $this->belongsTo(User::class, 'counselor_id');
    }
}
