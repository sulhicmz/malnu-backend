<?php

declare (strict_types = 1);

namespace App\Models\SchoolManagement;

use App\Models\ELearning\VirtualClass;
use App\Models\Grading\Grade;
use App\Models\Grading\Report;
use App\Models\Model;
use App\Models\OnlineExam\Exam;
use App\Traits\Cacheable;

class ClassModel extends Model
{
    use Cacheable;

    public const CACHE_TTL_MINUTES = 60; // 1 hour

    protected $primaryKey = 'id';
    protected $keyType    = 'string';
    public $incrementing  = false;

    protected $fillable = [
        'name',
        'level',
        'homeroom_teacher_id',
        'academic_year',
        'capacity',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    // Relationships
    public function homeroomTeacher()
    {
        return $this->belongsTo(Teacher::class, 'homeroom_teacher_id');
    }

    public function students()
    {
        return $this->hasMany(Student::class, 'class_id');
    }

    public function classSubjects()
    {
        return $this->hasMany(ClassSubject::class);
    }

    public function virtualClasses()
    {
        return $this->hasMany(VirtualClass::class);
    }

    public function grades()
    {
        return $this->hasMany(Grade::class);
    }

    public function reports()
    {
        return $this->hasMany(Report::class);
    }

    public function exams()
    {
        return $this->hasMany(Exam::class);
    }

    /**
     * Get all classes with caching
     */
    public static function getAllCached()
    {
        return static::getCached('all_classes', static::CACHE_TTL_MINUTES, function () {
            return static::all();
        });
    }

    /**
     * Get class by name with caching
     */
    public static function getByNameCached(string $name)
    {
        return static::getCached("name_{$name}", static::CACHE_TTL_MINUTES, function () use ($name) {
            return static::where('name', $name)->first();
        });
    }

    /**
     * Get class by academic year with caching
     */
    public static function getByAcademicYearCached(string $academicYear)
    {
        return static::getCached("academic_year_{$academicYear}", static::CACHE_TTL_MINUTES, function () use ($academicYear) {
            return static::where('academic_year', $academicYear)->get();
        });
    }

    /**
     * Clear class cache when saving
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
        static::forgetCached('all_classes');
        static::forgetCached("name_{$this->name}");
        static::forgetCached("academic_year_{$this->academic_year}");
    }
}
