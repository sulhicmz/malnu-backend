<?php

declare(strict_types=1);

namespace App\Models\LMS;

use App\Models\Model;
use App\Models\LMS\Course;
use App\Models\LMS\LearningPathItem;

class LearningPath extends Model
{
    protected $primaryKey = 'id';
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'name',
        'description',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function items()
    {
        return $this->hasMany(LearningPathItem::class);
    }

    public function courses()
    {
        return $this->hasManyThrough(Course::class, LearningPathItem::class);
    }
}
