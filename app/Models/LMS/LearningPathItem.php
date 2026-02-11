<?php

declare(strict_types=1);

namespace App\Models\LMS;

use App\Models\Model;
use App\Models\LMS\LearningPath;
use App\Models\LMS\Course;

class LearningPathItem extends Model
{
    protected $primaryKey = 'id';
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'learning_path_id',
        'course_id',
        'sort_order',
        'is_required',
    ];

    protected $casts = [
        'is_required' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function learningPath()
    {
        return $this->belongsTo(LearningPath::class);
    }

    public function course()
    {
        return $this->belongsTo(Course::class);
    }
}
