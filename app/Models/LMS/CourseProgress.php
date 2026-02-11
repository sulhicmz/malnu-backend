<?php

declare(strict_types=1);

namespace App\Models\LMS;

use App\Models\Model;
use App\Models\LMS\Enrollment;

class CourseProgress extends Model
{
    protected $primaryKey = 'id';
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'enrollment_id',
        'total_lessons',
        'completed_lessons',
        'total_assignments',
        'completed_assignments',
        'total_quizzes',
        'completed_quizzes',
        'progress_percentage',
        'last_activity_at',
    ];

    protected $casts = [
        'total_lessons' => 'integer',
        'completed_lessons' => 'integer',
        'total_assignments' => 'integer',
        'completed_assignments' => 'integer',
        'total_quizzes' => 'integer',
        'completed_quizzes' => 'integer',
        'progress_percentage' => 'decimal:2',
        'last_activity_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function enrollment()
    {
        return $this->belongsTo(Enrollment::class);
    }

    public function updateProgress(): void
    {
        $total = $this->total_lessons + $this->total_assignments + $this->total_quizzes;
        $completed = $this->completed_lessons + $this->completed_assignments + $this->completed_quizzes;
        
        $this->progress_percentage = $total > 0 ? ($completed / $total) * 100 : 0.00;
        $this->last_activity_at = now();
        $this->save();
    }
}
