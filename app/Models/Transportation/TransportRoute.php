<?php

declare(strict_types=1);

namespace App\Models\Transportation;

use App\Models\User;
use App\Models\Model;

class TransportRoute extends Model
{
    protected $table = 'transport_routes';

    protected $fillable = [
        'route_number',
        'name',
        'description',
        'start_location',
        'end_location',
        'departure_time',
        'arrival_time',
        'total_duration',
        'total_distance',
        'status',
        'stop_sequence',
        'capacity',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'departure_time' => 'datetime',
        'arrival_time' => 'datetime',
        'total_duration' => 'integer',
        'total_distance' => 'decimal:2',
        'stop_sequence' => 'array',
        'capacity' => 'integer',
        'created_by' => 'string',
        'updated_by' => 'string',
    ];

    public function routeStops()
    {
        return $this->hasMany(TransportRouteStop::class, 'route_id')->orderBy('sequence_order');
    }

    public function stops()
    {
        return $this->belongsToMany(TransportStop::class, 'transport_route_stops', 'route_id', 'stop_id')
            ->withPivot(['sequence_order', 'pickup_time', 'dropoff_time', 'distance_from_start', 'estimated_duration'])
            ->orderBy('sequence_order');
    }

    public function assignments()
    {
        return $this->hasMany(TransportAssignment::class, 'route_id');
    }

    public function schedules()
    {
        return $this->hasMany(TransportSchedule::class, 'route_id');
    }

    public function attendances()
    {
        return $this->hasMany(TransportAttendance::class, 'route_id');
    }

    public function trackingRecords()
    {
        return $this->hasMany(TransportTracking::class, 'route_id');
    }

    public function notifications()
    {
        return $this->hasMany(TransportNotification::class, 'route_id');
    }

    public function fees()
    {
        return $this->hasMany(TransportFee::class, 'route_id');
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
