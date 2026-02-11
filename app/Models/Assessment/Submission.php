<?php

declare(strict_types=1);

namespace App\Models\Assessment;

use App\Models\Assessment as AssessmentModel;
use App\Models\Grading\Grade;
use App\Models\Model;
use App\Models\SchoolManagement\Student;

class Submission extends Model
{
    protected $primaryKey = 'id';
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'assessment_id',
        'student_id',
        'started_at',
        'submitted_at',
        'time_spent_minutes',
        'score',
        'percentage',
        'passed',
        'feedback',
        'answers',
        'attempt_number',
        'status',
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'submitted_at' => 'datetime',
        'time_spent_minutes' => 'integer',
        'score' => 'decimal:2',
        'percentage' => 'decimal:2',
        'passed' => 'boolean',
        'answers' => 'array',
        'attempt_number' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function assessment()
    {
        return $this->belongsTo(AssessmentModel::class, 'assessment_id');
    }

    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function grades()
    {
        return $this->hasMany(Grade::class);
    }

    public function scopeInProgress($query)
    {
        return $query->where('status', 'in_progress');
    }

    public function scopeSubmitted($query)
    {
        return $query->where('status', 'submitted');
    }

    public function scopeGraded($query)
    {
        return $query->where('status', 'graded');
    }

    public function markAsGraded(float $score, string $feedback = null): void
    {
        $this->update([
            'score' => $score,
            'percentage' => $this->assessment->total_points > 0 
                ? ($score / $this->assessment->total_points) * 100 
                : 0,
            'passed' => $score >= $this->assessment->passing_grade,
            'feedback' => $feedback,
            'status' => 'graded',
        ]);
    }

    public function isLate(): bool
    {
        return $this->submitted_at && $this->assessment->end_time 
            ? $this->submitted_at->gt($this->assessment->end_time) 
            : false;
    }
}
