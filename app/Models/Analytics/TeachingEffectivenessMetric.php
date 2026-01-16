<?php

declare(strict_types=1);

namespace App\Models\Analytics;

use App\Models\Model;

class TeachingEffectivenessMetric extends Model
{
    protected $primaryKey = 'id';
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'teacher_id',
        'class_id',
        'semester',
        'student_count',
        'class_performance_improvement',
        'student_satisfaction_score',
        'assignments_graded',
        'feedback_provided',
        'notes',
        'evaluated_at',
    ];

    protected $casts = [
        'student_count' => 'integer',
        'class_performance_improvement' => 'decimal:2',
        'student_satisfaction_score' => 'decimal:2',
        'assignments_graded' => 'integer',
        'feedback_provided' => 'integer',
        'evaluated_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];
}
