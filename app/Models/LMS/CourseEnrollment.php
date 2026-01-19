<?php

declare(strict_types=1);

namespace App\Models\LMS;

use App\Models\Model;
use App\Models\SchoolManagement\Student;
use App\Models\User;

class CourseEnrollment extends Model
{
    protected $primaryKey = 'id';
    protected $keyType    = 'string';
    public $incrementing  = false;

    protected $fillable = [
        'course_id',
        'student_id',
        'enrollment_status',
        'progress_percentage',
        'lessons_completed',
        'total_lessons',
        'enrolled_at',
        'completed_at',
        'final_grade',
        'completion_notes',
    ];

    protected $casts = [
        'progress_percentage' => 'decimal:2',
        'lessons_completed'  => 'integer',
        'total_lessons'      => 'integer',
        'enrolled_at'        => 'date',
        'completed_at'        => 'date',
        'final_grade'         => 'decimal:2',
        'created_at'          => 'datetime',
        'updated_at'          => 'datetime',
    ];

    public function course()
    {
        return $this->belongsTo(Course::class);
    }

    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function learningProgress()
    {
        return $this->hasMany(LearningProgress::class);
    }

    public function scopeActive($query)
    {
        return $query->where('enrollment_status', 'active');
    }

    public function scopeCompleted($query)
    {
        return $query->where('enrollment_status', 'completed');
    }

    public function scopeByStudent($query, $studentId)
    {
        return $query->where('student_id', $studentId);
    }

    public function scopeByCourse($query, $courseId)
    {
        return $query->where('course_id', $courseId);
    }

    public function scopeInProgress($query)
    {
        return $query->whereIn('enrollment_status', ['pending', 'active'])
                    ->where('progress_percentage', '>', 0);
    }

    public function getIsActiveAttribute(): bool
    {
        return $this->enrollment_status === 'active';
    }

    public function getIsCompletedAttribute(): bool
    {
        return $this->enrollment_status === 'completed';
    }

    public function markAsActive(): void
    {
        $this->enrollment_status = 'active';
        $this->enrolled_at = now();
        $this->save();
    }

    public function updateProgress(float $percentage, int $lessonsCompleted): void
    {
        $this->progress_percentage = min(100, $percentage);
        $this->lessons_completed = $lessonsCompleted;
        if ($percentage >= 100) {
            $this->enrollment_status = 'completed';
            $this->completed_at = now();
        }
        $this->save();
    }
}
