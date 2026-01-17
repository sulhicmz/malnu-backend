<?php

declare(strict_types=1);

namespace App\Models\Extracurricular;

use App\Models\Model;
use App\Models\SchoolManagement\Student;
use App\Models\SchoolManagement\Teacher;

class Club extends Model
{
    protected $primaryKey = 'id';
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'name',
        'description',
        'category',
        'max_members',
        'advisor_id',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function advisor()
    {
        return $this->belongsTo(Teacher::class);
    }

    public function memberships()
    {
        return $this->hasMany(ClubMembership::class);
    }

    public function activities()
    {
        return $this->hasMany(Activity::class);
    }

    public function advisors()
    {
        return $this->hasMany(ClubAdvisor::class);
    }

    public function scopeByCategory($query, ?string $category)
    {
        if ($category) {
            return $query->where('category', $category);
        }
        return $query;
    }
}
