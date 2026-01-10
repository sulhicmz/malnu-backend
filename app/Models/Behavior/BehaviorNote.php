<?php

declare(strict_types=1);

namespace App\Models\Behavior;

use App\Models\Model;
use App\Models\SchoolManagement\Student;

class InterventionPlan extends Model
{
    protected $table = 'intervention_plans';

    protected $fillable = [
        'student_id',
        'incident_id',
        'created_by',
        'goals',
        'strategies',
        'timeline',
        'start_date',
        'end_date',
        'status',
        'evaluation',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function student()
    {
        return $this->belongsTo(Student::class, 'student_id');
    }

    public function incident()
    {
        return $this->belongsTo(Incident::class, 'incident_id');
    }

    public function createdBy()
    {
        return $this->belongsTo(\App\Models\User::class, 'created_by');
    }

    public function scopeByStudent($query, $studentId)
    {
        return $query->where('student_id', $studentId);
    }

    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }
}
