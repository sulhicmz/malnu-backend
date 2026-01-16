<?php

declare(strict_types=1);

namespace App\Models\Analytics;

use App\Models\Model;
use App\Models\SchoolManagement\Student;
use App\Models\SchoolManagement\ClassModel;
use App\Models\SchoolManagement\Subject;

class StudentPerformanceMetric extends Model
{
    protected $primaryKey = 'id';
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'student_id',
        'class_id',
        'subject_id',
        'semester',
        'gpa',
        'attendance_rate',
        'total_activities',
        'assignments_completed',
        'assignments_score',
        'quizzes_completed',
        'quizzes_score',
        'exams_completed',
        'exams_score',
        'engagement_score',
        'calculated_at',
        'metadata',
    ];

    protected $casts = [
        'gpa' => 'decimal:2',
        'attendance_rate' => 'decimal:2',
        'total_activities' => 'integer',
        'assignments_completed' => 'integer',
        'assignments_score' => 'integer',
        'quizzes_completed' => 'integer',
        'quizzes_score' => 'integer',
        'exams_completed' => 'integer',
        'exams_score' => 'integer',
        'engagement_score' => 'decimal:2',
        'calculated_at' => 'datetime',
        'metadata' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function class()
    {
        return $this->belongsTo(ClassModel::class);
    }

    public function subject()
    {
        return $this->belongsTo(Subject::class);
    }

    public function scopeByStudent($query, $studentId)
    {
        return $query->where('student_id', $studentId);
    }

    public function scopeByClass($query, $classId)
    {
        return $query->where('class_id', $classId);
    }

    public function scopeBySemester($query, $semester)
    {
        return $query->where('semester', $semester);
    }

    public function scopeBySubject($query, $subjectId)
    {
        return $query->where('subject_id', $subjectId);
    }

    public function getCalculatedGpaAttribute(): float
    {
        $totalScore = ($this->assignments_score + $this->quizzes_score + $this->exams_score);
        $totalMaxScore = ($this->assignments_completed * 100) + ($this->quizzes_completed * 100) + ($this->exams_completed * 100);
        
        return $totalMaxScore > 0 ? round($totalScore / $totalMaxScore, 2) : 0;
    }

    public function getOverallPerformanceAttribute(): string
    {
        $gpa = $this->getCalculatedGpaAttribute();
        
        if ($gpa >= 3.5) return 'excellent';
        if ($gpa >= 3.0) return 'very_good';
        if ($gpa >= 2.5) return 'good';
        if ($gpa >= 2.0) return 'satisfactory';
        if ($gpa >= 1.5) return 'needs_improvement';
        return 'at_risk';
    }
}