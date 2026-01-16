<?php

declare(strict_types=1);

namespace App\Models\Analytics;

use App\Models\Model;
use App\Models\SchoolManagement\Student;

class LearningPattern extends Model
{
    protected $primaryKey = 'id';
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'student_id',
        'pattern_type',
        'pattern_value',
        'pattern_frequency',
        'occurrence_count',
        'start_date',
        'end_date',
        'metrics',
    ];

    protected $casts = [
        'occurrence_count' => 'integer',
        'start_date' => 'date',
        'end_date' => 'date',
        'metrics' => 'array',
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
        return $query->where('pattern_type', $type);
    }
}