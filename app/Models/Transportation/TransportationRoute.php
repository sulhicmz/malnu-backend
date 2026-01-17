<?php

declare(strict_types=1);

namespace App\Models\Transportation;

use App\Models\Model;

class TransportationRoute extends Model
{
    protected $primaryKey = 'id';
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'route_name',
        'route_description',
        'origin',
        'destination',
        'stops',
        'departure_time',
        'arrival_time',
        'capacity',
        'current_enrollment',
        'bus_number',
        'status',
    ];

    protected $casts = [
        'stops' => 'array',
        'departure_time' => 'datetime',
        'arrival_time' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function registrations()
    {
        return $this->hasMany(TransportationRegistration::class);
    }

    public function assignments()
    {
        return $this->hasMany(TransportationAssignment::class);
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeAvailable($query)
    {
        return $query->where('status', 'active')
            ->whereColumn('current_enrollment', '<', 'capacity');
    }
}
