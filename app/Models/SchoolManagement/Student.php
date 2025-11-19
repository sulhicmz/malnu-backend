<?php

declare (strict_types = 1);

namespace App\Models\SchoolManagement;

use App\Models\CareerDevelopment\CareerAssessment;
use App\Models\CareerDevelopment\CounselingSession;
use App\Models\Grading\Competency;
use App\Models\Grading\Grade;
use App\Models\Grading\Report;
use App\Models\Grading\StudentPortfolio;
use App\Models\Model;
use App\Models\OnlineExam\ExamResult;
use App\Models\User;
use App\Traits\Cacheable;

class Student extends Model
{
    use Cacheable;

    public const CACHE_TTL_MINUTES = 60; // 1 hour

    protected $primaryKey = 'id';
    protected $keyType    = 'string';
    public $incrementing  = false;

    protected $fillable = [
        'user_id',
        'nisn',
        'class_id',
        'birth_date',
        'birth_place',
        'address',
        'parent_id',
        'enrollment_date',
        'status',
    ];

    protected $casts = [
        'birth_date'      => 'date',
        'enrollment_date' => 'date',
        'created_at'      => 'datetime',
        'updated_at'      => 'datetime',
    ];

    // Relationships
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function class ()
    {
        return $this->belongsTo(ClassModel::class);
    }

    public function parent()
    {
        return $this->belongsTo(Parent::class);
    }

    public function grades()
    {
        return $this->hasMany(Grade::class);
    }

    public function competencies()
    {
        return $this->hasMany(Competency::class);
    }

    public function reports()
    {
        return $this->hasMany(Report::class);
    }

    public function portfolios()
    {
        return $this->hasMany(StudentPortfolio::class);
    }

    public function examResults()
    {
        return $this->hasMany(ExamResult::class);
    }

    public function careerAssessments()
    {
        return $this->hasMany(CareerAssessment::class);
    }

    public function counselingSessions()
    {
        return $this->hasMany(CounselingSession::class);
    }

    /**
     * Get all students with caching
     */
    public static function getAllCached()
    {
        return static::getCached('all_students', static::CACHE_TTL_MINUTES, function () {
            return static::all();
        });
    }

    /**
     * Get student by user_id with caching
     */
    public static function getByUserIdCached(string $userId)
    {
        return static::getCached("user_id_{$userId}", static::CACHE_TTL_MINUTES, function () use ($userId) {
            return static::where('user_id', $userId)->first();
        });
    }

    /**
     * Get students by class_id with caching
     */
    public static function getByClassIdCached(string $classId)
    {
        return static::getCached("class_id_{$classId}", static::CACHE_TTL_MINUTES, function () use ($classId) {
            return static::where('class_id', $classId)->get();
        });
    }

    /**
     * Clear student cache when saving
     */
    protected static function boot()
    {
        parent::boot();

        static::saved(function ($model) {
            $model->clearRelatedCache();
        });

        static::deleted(function ($model) {
            $model->clearRelatedCache();
        });
    }

    /**
     * Clear related cache entries
     */
    public function clearRelatedCache()
    {
        static::forgetCached('all_students');
        static::forgetCached("user_id_{$this->user_id}");
        if ($this->class_id) {
            static::forgetCached("class_id_{$this->class_id}");
        }
    }
}
