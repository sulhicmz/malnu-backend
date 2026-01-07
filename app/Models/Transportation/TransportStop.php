<?php

declare(strict_types=1);

namespace App\Models\Transportation;

use App\Models\User;
use App\Models\Model;

class TransportStop extends Model
{
    protected $table = 'transport_stops';

    protected $fillable = [
        'name',
        'address',
        'latitude',
        'longitude',
        'landmark',
        'estimated_time',
        'is_active',
        'metadata',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'latitude' => 'decimal:7',
        'longitude' => 'decimal:7',
        'estimated_time' => 'integer',
        'is_active' => 'boolean',
        'metadata' => 'array',
        'created_by' => 'string',
        'updated_by' => 'string',
    ];

    public function routeStops()
    {
        return $this->hasMany(TransportRouteStop::class, 'stop_id');
    }

    public function pickupAssignments()
    {
        return $this->hasMany(TransportAssignment::class, 'pickup_stop_id');
    }

    public function dropoffAssignments()
    {
        return $this->hasMany(TransportAssignment::class, 'dropoff_stop_id');
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updatedBy()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
}
