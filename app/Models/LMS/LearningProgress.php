<?php

declare(strict_types=1);

namespace App\Models\LMS;

use App\Models\ELearning\LearningMaterial;
use App\Models\ELearning\Assignment;
use App\Models\ELearning\Quiz;
use App\Models\Model;

class LearningProgress extends Model
{
    protected $primaryKey = 'id';
    protected $keyType    = 'string';
    public $incrementing  = false;

    protected $fillable = [
        'course_enrollment_id',
        'learning_material_id',
        'assignment_id',
        'quiz_id',
        'status',
        'score',
        'time_spent_minutes',
        'attempts',
        'started_at',
        'completed_at',
        'last_accessed_at',
        'notes',
    ];

    protected $casts = [
        'score'              => 'decimal:2',
        'time_spent_minutes' => 'integer',
        'attempts'           => 'integer',
        'started_at'          => 'datetime',
        'completed_at'         => 'datetime',
        'last_accessed_at'    => 'datetime',
        'created_at'          => 'datetime',
        'updated_at'          => 'datetime',
    ];

    public function enrollment()
    {
        return $this->belongsTo(CourseEnrollment::class, 'course_enrollment_id');
    }

    public function learningMaterial()
    {
        return $this->belongsTo(LearningMaterial::class);
    }

    public function assignment()
    {
        return $this->belongsTo(Assignment::class);
    }

    public function quiz()
    {
        return $this->belongsTo(Quiz::class);
    }

    public function scopeInProgress($query)
    {
        return $query->where('status', 'in_progress');
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    public function scopeByEnrollment($query, $enrollmentId)
    {
        return $query->where('course_enrollment_id', $enrollmentId);
    }

    public function scopeByType($query, $type)
    {
        return $query->whereNotNull($type . '_id');
    }

    public function markAsStarted(): void
    {
        if ($this->status === 'not_started') {
            $this->status = 'in_progress';
            $this->started_at = now();
            $this->save();
        }
    }

    public function markAsCompleted(?float $score = null): void
    {
        $this->status = 'completed';
        $this->completed_at = now();
        $this->last_accessed_at = now();
        
        if ($score !== null) {
            $this->score = $score;
        }
        
        $this->save();
    }

    public function recordAccess(int $minutes): void
    {
        $this->time_spent_minutes += $minutes;
        $this->last_accessed_at = now();
        
        if ($this->status === 'not_started') {
            $this->markAsStarted();
        }
        
        $this->save();
    }

    public function getIsStartedAttribute(): bool
    {
        return $this->status !== 'not_started';
    }

    public function getIsCompletedAttribute(): bool
    {
        return $this->status === 'completed';
    }

    public function getTypeAttribute(): ?string
    {
        if ($this->learning_material_id !== null) {
            return 'learning_material';
        }
        if ($this->assignment_id !== null) {
            return 'assignment';
        }
        if ($this->quiz_id !== null) {
            return 'quiz';
        }
        return null;
    }
}
