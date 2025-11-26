<?php

declare(strict_types=1);

namespace App\Models\SchoolManagement;

use App\Models\Model;
use App\Models\SchoolManagement\ClassModel;
use App\Models\SchoolManagement\Subject;
use App\Models\SchoolManagement\Teacher;
use App\Models\SchoolManagement\Schedule;

class ClassSubject extends Model
{
    protected $primaryKey = 'id';
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'class_id',
        'subject_id',
        'teacher_id',
        'schedule_info',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    // Relationships
    public function class()
    {
        return $this->belongsTo(ClassModel::class);
    }

    public function subject()
    {
        return $this->belongsTo(Subject::class);
    }

    public function teacher()
    {
        return $this->belongsTo(Teacher::class);
    }

    public function schedules()
    {
        return $this->hasMany(Schedule::class);
    }
}
