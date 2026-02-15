<?php

declare(strict_types=1);

namespace App\Models\Analytics;

use App\Models\Model;
use App\Models\SchoolManagement\Student;
use App\Models\SchoolManagement\Subject;

class LearningActivity extends Model
{
    public $incrementing = false;

    protected $primaryKey = 'id';

    protected $keyType = 'string';

    protected $table = 'learning_activities';

    protected $fillable = [
        'student_id',
        'subject_id',
        'activity_type',
        'activity_name',
        'description',
        'score',
        'max_score',
        'activity_date',
        'duration_minutes',
        'metadata',
    ];

    protected $casts = [
        'score' => 'decimal:2',
        'max_score' => 'decimal:2',
        'activity_date' => 'datetime',
        'metadata' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function subject()
    {
        return $this->belongsTo(Subject::class);
    }

    public function getPercentageScoreAttribute(): ?float
    {
        if ($this->max_score && $this->max_score > 0) {
            return round(($this->score / $this->max_score) * 100, 2);
        }
        return null;
    }
}
