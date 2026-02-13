<?php

declare(strict_types=1);

namespace App\Models\SchoolManagement;

use App\Models\Model;

class TeacherWorkload extends Model
{
    public $incrementing = false;

    protected $primaryKey = 'id';

    protected $keyType = 'string';

    protected $table = 'teacher_workloads';

    protected $fillable = [
        'teacher_id',
        'academic_year',
        'semester',
        'total_hours_per_week',
        'max_hours_per_week',
        'teaching_hours',
        'administrative_hours',
        'extracurricular_hours',
        'preparation_hours',
        'grading_hours',
        'other_duties_hours',
        'workload_status',
        'notes',
    ];

    protected $casts = [
        'total_hours_per_week' => 'float',
        'max_hours_per_week' => 'float',
        'teaching_hours' => 'float',
        'administrative_hours' => 'float',
        'extracurricular_hours' => 'float',
        'preparation_hours' => 'float',
        'grading_hours' => 'float',
        'other_duties_hours' => 'float',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    // Relationships
    public function teacher()
    {
        return $this->belongsTo(Teacher::class);
    }

    // Accessors
    public function getUtilizationPercentageAttribute(): float
    {
        if ($this->max_hours_per_week <= 0) {
            return 0.0;
        }

        return round(($this->total_hours_per_week / $this->max_hours_per_week) * 100, 2);
    }

    public function getRemainingHoursAttribute(): float
    {
        return max(0, $this->max_hours_per_week - $this->total_hours_per_week);
    }

    public function isOverloaded(): bool
    {
        return $this->total_hours_per_week > $this->max_hours_per_week;
    }

    public function isUnderloaded(): bool
    {
        return $this->total_hours_per_week < ($this->max_hours_per_week * 0.5);
    }
}
