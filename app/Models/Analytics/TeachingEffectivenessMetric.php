<?php

declare(strict_types=1);

namespace App\Models\Analytics;

use App\Models\Model;
use App\Models\SchoolManagement\ClassModel;
use App\Models\SchoolManagement\Subject;
use App\Models\SchoolManagement\Teacher;

class TeachingEffectivenessMetric extends Model
{
    public $incrementing = false;

    protected $primaryKey = 'id';

    protected $keyType = 'string';

    protected $table = 'teaching_effectiveness_metrics';

    protected $fillable = [
        'teacher_id',
        'class_id',
        'subject_id',
        'class_average_improvement',
        'student_engagement_score',
        'assessment_quality_score',
        'total_students',
        'students_improved',
        'period_type',
        'period_start',
        'period_end',
    ];

    protected $casts = [
        'class_average_improvement' => 'decimal:2',
        'student_engagement_score' => 'decimal:2',
        'assessment_quality_score' => 'decimal:2',
        'period_start' => 'date',
        'period_end' => 'date',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function teacher()
    {
        return $this->belongsTo(Teacher::class);
    }

    public function class()
    {
        return $this->belongsTo(ClassModel::class);
    }

    public function subject()
    {
        return $this->belongsTo(Subject::class);
    }

    public function getImprovementRateAttribute(): float
    {
        if ($this->total_students > 0) {
            return round(($this->students_improved / $this->total_students) * 100, 2);
        }
        return 0.00;
    }
}
