<?php

declare(strict_types=1);

namespace App\Models\AlumniManagement;

use App\Models\Model;

class AlumniCareer extends Model
{
    protected $primaryKey = 'id';
    protected $keyType    = 'string';
    public $incrementing  = false;

    protected $fillable = [
        'alumni_id',
        'company',
        'position',
        'industry',
        'start_date',
        'end_date',
        'current_job',
        'location',
        'description',
    ];

    protected $casts = [
        'current_job'    => 'boolean',
        'start_date'     => 'date',
        'end_date'       => 'date',
        'created_at'      => 'datetime',
        'updated_at'      => 'datetime',
        'deleted_at'      => 'datetime',
    ];

    public function alumni()
    {
        return $this->belongsTo(AlumniProfile::class, 'alumni_id');
    }

    public function scopeCurrentJobs($query)
    {
        return $query->where('current_job', true);
    }

    public function scopeByIndustry($query, $industry)
    {
        return $query->where('industry', 'like', "%{$industry}%");
    }

    public function scopeByCompany($query, $company)
    {
        return $query->where('company', 'like', "%{$company}%");
    }
}
