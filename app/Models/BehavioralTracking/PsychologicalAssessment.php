<?php

declare(strict_types=1);

namespace App\Models\BehavioralTracking;

use App\Models\Model;
use App\Models\SchoolManagement\Student;
use App\Models\User;

class PsychologicalAssessment extends Model
{
    protected $primaryKey = 'id';
    protected $keyType    = 'string';
    public $incrementing  = false;

    protected $fillable = [
        'student_id',
        'assessed_by',
        'assessment_type',
        'assessment_data',
        'score',
        'max_score',
        'notes',
        'recommendations',
        'is_confidential',
        'assessment_date',
    ];

    protected $casts = [
        'assessment_data'  => 'array',
        'is_confidential'    => 'boolean',
        'assessment_date'   => 'datetime',
        'created_at'       => 'datetime',
        'updated_at'       => 'datetime',
    ];

    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function assessedBy()
    {
        return $this->belongsTo(User::class, 'assessed_by');
    }
}
