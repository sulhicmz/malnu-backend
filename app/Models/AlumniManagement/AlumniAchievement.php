<?php

declare(strict_types=1);

namespace App\Models\AlumniManagement;

use App\Models\Model;

class AlumniAchievement extends Model
{
    protected $primaryKey = 'id';
    protected $keyType    = 'string';
    public $incrementing  = false;

    protected $fillable = [
        'alumni_id',
        'achievement_type',
        'title',
        'description',
        'achievement_date',
        'link',
    ];

    protected $casts = [
        'achievement_date' => 'date',
        'created_at'     => 'datetime',
        'updated_at'     => 'datetime',
        'deleted_at'     => 'datetime',
    ];

    public function alumni()
    {
        return $this->belongsTo(AlumniProfile::class, 'alumni_id');
    }

    public function scopeByType($query, $type)
    {
        return $query->where('achievement_type', $type);
    }
}
