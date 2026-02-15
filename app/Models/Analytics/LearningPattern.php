<?php

declare(strict_types=1);

namespace App\Models\Analytics;

use App\Models\Model;
use App\Models\SchoolManagement\Student;

class LearningPattern extends Model
{
    public $incrementing = false;

    protected $primaryKey = 'id';

    protected $keyType = 'string';

    protected $table = 'learning_patterns';

    protected $fillable = [
        'student_id',
        'pattern_type',
        'pattern_data',
        'analysis_period_start',
        'analysis_period_end',
        'pattern_strength',
        'insights',
    ];

    protected $casts = [
        'pattern_data' => 'array',
        'analysis_period_start' => 'date',
        'analysis_period_end' => 'date',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function hasStrongPattern(): bool
    {
        return $this->pattern_strength === 'strong';
    }
}
