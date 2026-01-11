<?php

namespace App\Models;

use Hyperf\DbConnection\Model\Model;

class InstitutionalMetric extends Model
{
    protected $table = 'institutional_metrics';

    protected $fillable = [
        'metric_name',
        'metric_type',
        'category',
        'value',
        'unit',
        'metric_date',
        'academic_year',
        'comparison_period',
        'previous_value',
        'target_value',
        'trend',
        'notes',
        'data_source_staff_id',
    ];

    protected $casts = [
        'value' => 'decimal:2',
        'previous_value' => 'decimal:2',
        'target_value' => 'decimal:2',
        'metric_date' => 'date',
    ];

    public function dataSourceStaff()
    {
        return $this->belongsTo(Staff::class, 'data_source_staff_id');
    }

    public function scopeByCategory($query, string $category)
    {
        return $query->where('category', $category);
    }

    public function scopeByType($query, string $type)
    {
        return $query->where('metric_type', $type);
    }

    public function scopeByAcademicYear($query, string $year)
    {
        return $query->where('academic_year', $year);
    }

    public function getTrend(): ?string
    {
        if ($this->previous_value === null) {
            return null;
        }

        if ($this->value > $this->previous_value) {
            return 'up';
        } elseif ($this->value < $this->previous_value) {
            return 'down';
        }

        return 'stable';
    }
}
