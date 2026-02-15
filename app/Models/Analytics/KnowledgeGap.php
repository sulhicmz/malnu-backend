<?php

declare(strict_types=1);

namespace App\Models\Analytics;

use App\Models\Model;
use App\Models\SchoolManagement\Student;
use App\Models\SchoolManagement\Subject;

class KnowledgeGap extends Model
{
    public $incrementing = false;

    protected $primaryKey = 'id';

    protected $keyType = 'string';

    protected $table = 'knowledge_gaps';

    protected $fillable = [
        'student_id',
        'subject_id',
        'topic_area',
        'sub_topic',
        'mastery_level',
        'target_mastery_level',
        'gap_status',
        'assessment_count',
        'last_assessed_at',
        'recommended_resources',
    ];

    protected $casts = [
        'mastery_level' => 'decimal:2',
        'target_mastery_level' => 'decimal:2',
        'last_assessed_at' => 'datetime',
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

    public function getGapPercentageAttribute(): float
    {
        $gap = $this->target_mastery_level - $this->mastery_level;
        return max(0, round($gap, 2));
    }

    public function isCritical(): bool
    {
        return $this->gap_status === 'critical' || $this->mastery_level < 40;
    }

    public function isResolved(): bool
    {
        return $this->gap_status === 'resolved' || $this->mastery_level >= $this->target_mastery_level;
    }
}
