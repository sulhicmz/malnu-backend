<?php

declare (strict_types = 1);

namespace App\Models\Grading;

use App\Models\Model;
use App\Models\User;

class ReportTemplate extends Model
{
    protected $primaryKey = 'id';
    protected $keyType    = 'string';
    public $incrementing  = false;

    protected $fillable = [
        'name',
        'type',
        'html_template',
        'variables',
        'grade_level',
        'is_active',
        'created_by',
    ];

    protected $casts = [
        'variables'  => 'array',
        'is_active'  => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeByType($query, string $type)
    {
        return $query->where('type', $type);
    }

    public function scopeByGradeLevel($query, ?string $gradeLevel)
    {
        if ($gradeLevel) {
            return $query->where('grade_level', $gradeLevel);
        }
        return $query->whereNull('grade_level');
    }
}
