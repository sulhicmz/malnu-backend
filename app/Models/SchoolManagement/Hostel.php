<?php

declare(strict_types = 1);

namespace App\Models\SchoolManagement;

use App\Models\Model;

class Hostel extends Model
{
    protected $primaryKey = 'id';
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'name',
        'description',
        'location',
        'total_capacity',
        'warden_name',
        'warden_contact',
        'status',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function rooms()
    {
        return $this->hasMany(HostelRoom::class);
    }

    public function allocations()
    {
        return $this->hasManyThrough(HostelAllocation::class, HostelRoom::class);
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeAvailable($query)
    {
        return $query->where('status', 'active')
            ->whereHas('rooms', function ($roomQuery) {
                $roomQuery->where('status', 'available');
            });
    }
}
