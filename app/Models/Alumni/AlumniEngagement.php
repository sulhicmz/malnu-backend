<?php

declare(strict_types=1);

namespace App\Models\Alumni;

use App\Models\Model;

class AlumniEngagement extends Model
{
    protected $table = 'alumni_engagements';

    protected $fillable = [
        'alumni_id',
        'engagement_type',
        'description',
        'engagement_date',
        'category',
        'details',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'engagement_date' => 'datetime',
        'details' => 'array',
        'created_by' => 'string',
        'updated_by' => 'string',
    ];

    public function alumni()
    {
        return $this->belongsTo(Alumni::class, 'alumni_id');
    }

    public function scopeByType($query, $type)
    {
        return $query->where('engagement_type', $type);
    }

    public function scopeByCategory($query, $category)
    {
        return $query->where('category', $category);
    }

    public function scopeByYear($query, $year)
    {
        return $query->whereYear('engagement_date', $year);
    }

    public function scopeRecent($query, $days = 30)
    {
        return $query->where('engagement_date', '>=', now()->subDays($days));
    }
}
