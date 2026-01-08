<?php

declare (strict_types = 1);

namespace App\Models\Grading;

use App\Models\Model;
use App\Models\SchoolManagement\Student;
use App\Models\SchoolManagement\Subject;
use App\Models\User;

class Competency extends Model
{
    protected string $primaryKey = 'id';
    protected string $keyType = 'string';
    public bool $incrementing = false;

    protected $fillable = [
        'student_id',
        'subject_id',
        'competency_code',
        'competency_name',
        'achievement_level',
        'semester',
        'notes',
        'created_by',
    ];

    protected $casts = [
        'semester'   => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    // Relationships
    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function subject()
    {
        return $this->belongsTo(Subject::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
