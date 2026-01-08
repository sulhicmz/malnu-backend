<?php

declare(strict_types=1);

namespace App\Models\Alumni;

use App\Models\Model;

class AlumniCareer extends Model
{
    protected $table = 'alumni_careers';

    protected $fillable = [
        'alumni_id',
        'company_name',
        'position',
        'industry',
        'start_date',
        'end_date',
        'is_current',
        'description',
        'achievements',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'is_current' => 'boolean',
        'start_date' => 'date',
        'end_date' => 'date',
        'created_by' => 'string',
        'updated_by' => 'string',
    ];

    public function alumni()
    {
        return $this->belongsTo(Alumni::class, 'alumni_id');
    }

    public function scopeCurrent($query)
    {
        return $query->where('is_current', true);
    }

    public function scopePast($query)
    {
        return $query->where('is_current', false);
    }

    public function scopeByIndustry($query, $industry)
    {
        return $query->where('industry', $industry);
    }

    public function getDurationAttribute()
    {
        if ($this->start_date) {
            $end = $this->end_date ?: now();
            return $this->start_date->diff($end)->format('%y years, %m months');
        }
        return null;
    }
}
