<?php

declare(strict_types=1);

namespace App\Models\LMS;

use App\Models\Model;
use App\Models\SchoolManagement\Subject;
use App\Models\SchoolManagement\Teacher;
use App\Models\User;

class Course extends Model
{
    protected $primaryKey = 'id';
    protected $keyType    = 'string';
    public $incrementing  = false;

    protected $fillable = [
        'subject_id',
        'teacher_id',
        'code',
        'name',
        'description',
        'credits',
        'duration_weeks',
        'level',
        'status',
        'start_date',
        'end_date',
        'max_students',
        'allow_enrollment',
        'is_active',
    ];

    protected $casts = [
        'credits'          => 'integer',
        'duration_weeks'  => 'integer',
        'max_students'    => 'integer',
        'allow_enrollment' => 'boolean',
        'is_active'       => 'boolean',
        'start_date'       => 'date',
        'end_date'         => 'date',
        'created_at'       => 'datetime',
        'updated_at'       => 'datetime',
    ];

    public function subject()
    {
        return $this->belongsTo(Subject::class);
    }

    public function teacher()
    {
        return $this->belongsTo(Teacher::class);
    }

    public function enrollments()
    {
        return $this->hasMany(CourseEnrollment::class);
    }

    public function learningProgress()
    {
        return $this->hasManyThrough(LearningProgress::class, CourseEnrollment::class);
    }

    public function activeEnrollments()
    {
        return $this->hasMany(CourseEnrollment::class)->where('enrollment_status', 'active');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true)->where('status', 'published');
    }

    public function scopePublished($query)
    {
        return $query->where('status', 'published');
    }

    public function scopeBySubject($query, $subjectId)
    {
        return $query->where('subject_id', $subjectId);
    }

    public function scopeByTeacher($query, $teacherId)
    {
        return $query->where('teacher_id', $teacherId);
    }

    public function getEnrolledCountAttribute(): int
    {
        return $this->enrollments()->where('enrollment_status', 'active')->count();
    }

    public function getAvailableSlotsAttribute(): int
    {
        $enrolled = $this->enrolled_count;
        $max = $this->max_students;
        return $max !== null ? max(0, $max - $enrolled) : 999;
    }

    public function getIsFullAttribute(): bool
    {
        return $this->available_slots === 0;
    }
}
