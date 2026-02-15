<?php

declare(strict_types=1);

namespace App\Models\Analytics;

use App\Models\Model;
use App\Models\SchoolManagement\Student;
use App\Models\SchoolManagement\Subject;

class StudentPerformanceMetric extends Model
{
    public $incrementing = false;

    protected $primaryKey = 'id';

    protected $keyType = 'string';

    protected $table = 'student_performance_metrics';

    protected $fillable = [
        'student_id',
        'subject_id',
        'metric_type',
        'value',
        'period_type',
        'period_start',
        'period_end',
        'previous_value',
        'trend_percentage',
        'breakdown',
    ];

    protected $casts = [
        'value' => 'decimal:4',
        'previous_value' => 'decimal:4',
        'trend_percentage' => 'decimal:2',
        'period_start' => 'date',
        'period_end' => 'date',
        'breakdown' => 'array',
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

    public function isImproving(): bool
    {
        return $this->trend_percentage > 0;
    }

    public function isDeclining(): bool
    {
        return $this->trend_percentage < 0;
    }
}
