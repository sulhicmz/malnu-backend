<?php

declare(strict_types=1);

namespace App\Models\Assessment;

use App\Models\Assessment as AssessmentModel;
use App\Models\Model;
use App\Models\SchoolManagement\Student;

class Analytics extends Model
{
    protected $primaryKey = 'id';
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'assessment_id',
        'student_id',
        'total_participants',
        'completed_count',
        'average_score',
        'highest_score',
        'lowest_score',
        'pass_rate',
        'average_time_minutes',
        'question_performance',
        'learning_outcomes',
    ];

    protected $casts = [
        'total_participants' => 'integer',
        'completed_count' => 'integer',
        'average_score' => 'decimal:2',
        'highest_score' => 'decimal:2',
        'lowest_score' => 'decimal:2',
        'pass_rate' => 'integer',
        'average_time_minutes' => 'decimal:2',
        'question_performance' => 'array',
        'learning_outcomes' => 'array',
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

    public function getCompletionRate(): float
    {
        return $this->total_participants > 0 
            ? ($this->completed_count / $this->total_participants) * 100 
            : 0;
    }
}
