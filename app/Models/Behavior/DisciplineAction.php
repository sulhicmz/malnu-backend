<?php

declare(strict_types=1);

namespace App\Models\Behavior;

use App\Models\Model;
use App\Models\SchoolManagement\Student;

class Incident extends Model
{
    protected $table = 'behavior_incidents';

    protected $fillable = [
        'student_id',
        'category_id',
        'reported_by',
        'title',
        'description',
        'incident_date',
        'incident_time',
        'location',
        'severity',
        'status',
        'evidence',
    ];

    protected $casts = [
        'incident_date' => 'date',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function student()
    {
        return $this->belongsTo(Student::class, 'student_id');
    }

    public function category()
    {
        return $this->belongsTo(BehaviorCategory::class, 'category_id');
    }

    public function reportedBy()
    {
        return $this->belongsTo(\App\Models\User::class, 'reported_by');
    }

    public function disciplineActions()
    {
        return $this->hasMany(DisciplineAction::class, 'incident_id');
    }

    public function interventionPlan()
    {
        return $this->hasOne(InterventionPlan::class, 'incident_id');
    }

    public function scopeByStudent($query, $studentId)
    {
        return $query->where('student_id', $studentId);
    }

    public function scopeByDate($query, $date)
    {
        return $query->whereDate('incident_date', $date);
    }

    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    public function scopeBySeverity($query, $severity)
    {
        return $query->where('severity', $severity);
    }
}
