<?php

declare(strict_types=1);

namespace App\Models\LMS;

use App\Models\Model;
use App\Models\ELearning\VirtualClass;
use App\Models\LMS\Enrollment;
use App\Models\LMS\LearningPathItem;
use App\Models\LMS\Certificate;

class Course extends Model
{
    protected $primaryKey = 'id';
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'virtual_class_id',
        'name',
        'description',
        'code',
        'level',
        'duration_hours',
        'is_published',
        'start_date',
        'end_date',
    ];

    protected $casts = [
        'is_published' => 'boolean',
        'start_date' => 'date',
        'end_date' => 'date',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function virtualClass()
    {
        return $this->belongsTo(VirtualClass::class);
    }

    public function enrollments()
    {
        return $this->hasMany(Enrollment::class);
    }

    public function learningPathItems()
    {
        return $this->hasMany(LearningPathItem::class);
    }

    public function certificates()
    {
        return $this->hasMany(Certificate::class);
    }
}
