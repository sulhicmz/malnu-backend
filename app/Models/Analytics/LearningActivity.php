<?php

declare(strict_types=1);

namespace App\Models\Analytics;

use App\Models\Model;
use App\Models\SchoolManagement\Student;

class LearningActivity extends Model
{
    protected $primaryKey = 'id';
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'student_id',
        'activity_type',
        'activity_subtype',
        'title',
        'description',
        'duration_minutes',
        'score',
        'max_score',
        'related_entity_type',
        'related_entity_id',
        'activity_date',
        'metadata',
    ];

    protected $casts = [
        'duration_minutes' => 'integer',
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

    public function scopeByStudent($query, $studentId)
    {
        return $query->where('student_id', $studentId);
    }

    public function scopeByType($query, $type)
    {
        return $query->where('activity_type', $type);
    }

    public function scopeBetweenDates($query, $startDate, $endDate)
    {
        return $query->whereBetween('activity_date', [$startDate, $endDate]);
    }
}