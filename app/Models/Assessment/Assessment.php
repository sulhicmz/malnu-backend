<?php

declare(strict_types=1);

namespace App\Models\Assessment;

use App\Models\Grading\Grade;
use App\Models\Model;
use App\Models\SchoolManagement\ClassModel;
use App\Models\SchoolManagement\Subject;
use App\Models\User;
use Carbon\Carbon;

class Assessment extends Model
{
    protected $primaryKey = 'id';
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'title',
        'assessment_type',
        'description',
        'subject_id',
        'class_id',
        'start_time',
        'end_time',
        'duration_minutes',
        'total_points',
        'passing_grade',
        'is_published',
        'allow_retakes',
        'max_attempts',
        'shuffle_questions',
        'show_results_immediately',
        'proctoring_enabled',
        'rubric_id',
        'created_by',
    ];

    protected $casts = [
        'start_time' => 'datetime',
        'end_time' => 'datetime',
        'total_points' => 'decimal:2',
        'passing_grade' => 'decimal:2',
        'is_published' => 'boolean',
        'allow_retakes' => 'boolean',
        'max_attempts' => 'integer',
        'shuffle_questions' => 'boolean',
        'show_results_immediately' => 'boolean',
        'proctoring_enabled' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function subject()
    {
        return $this->belongsTo(Subject::class);
    }

    public function class()
    {
        return $this->belongsTo(ClassModel::class, 'class_id');
    }

    public function rubric()
    {
        return $this->belongsTo(Rubric::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function submissions()
    {
        return $this->hasMany(Submission::class, 'assessment_id');
    }

    public function analytics()
    {
        return $this->hasMany(Analytics::class, 'assessment_id');
    }

    public function grades()
    {
        return $this->hasMany(Grade::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_published', true);
    }

    public function scopeUpcoming($query)
    {
        return $query->where('start_time', '>', Carbon::now());
    }

    public function scopeOngoing($query)
    {
        return $query->where('start_time', '<=', Carbon::now())
            ->where('end_time', '>=', Carbon::now());
    }

    public function scopeEnded($query)
    {
        return $query->where('end_time', '<', Carbon::now());
    }

    public function isAccessibleBy(User $user): bool
    {
        $student = $user->student;
        if ($user->hasRole('admin') || $user->hasRole('teacher')) {
            return true;
        }

        return $student && $this->class_id === $student->class_id && $this->is_published;
    }
}
