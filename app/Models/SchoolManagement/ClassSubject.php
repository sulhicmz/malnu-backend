<?php

declare(strict_types=1);

namespace App\Models\SchoolManagement;

use App\Models\Model;

class ClassSubject extends Model
{
    public $incrementing = false;

    protected $primaryKey = 'id';

    protected $keyType = 'string';

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
