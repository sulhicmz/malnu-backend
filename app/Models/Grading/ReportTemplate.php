<?php

declare(strict_types=1);

namespace App\Models\Grading;

use App\Models\Model;
use App\Models\User;

class ReportTemplate extends Model
{
    public $incrementing = false;

    protected $primaryKey = 'id';

    protected $keyType = 'string';

    protected $fillable = [
        'name',
        'description',
        'type',
        'grade_level',
        'header_template',
        'content_template',
        'footer_template',
        'css_styles',
        'placeholders',
        'is_default',
        'is_active',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'placeholders' => 'array',
        'is_default' => 'boolean',
        'is_active' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function reports()
    {
        return $this->hasMany(Report::class, 'template_id');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeDefault($query)
    {
        return $query->where('is_default', true);
    }

    public function scopeForType($query, string $type)
    {
        return $query->where('type', $type);
    }

    public function scopeForGradeLevel($query, ?string $gradeLevel)
    {
        return $query->where(function ($q) use ($gradeLevel) {
            $q->whereNull('grade_level')
              ->orWhere('grade_level', $gradeLevel);
        });
    }
}
