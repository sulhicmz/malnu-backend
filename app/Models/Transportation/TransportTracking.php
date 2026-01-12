<?php

declare(strict_types=1);

namespace App\Models\Transportation;

use App\Models\Model;

class TransportTracking extends Model
{
    protected $table = 'transport_tracking';

    protected $fillable = [
        'vehicle_id',
        'route_id',
        'driver_id',
        'latitude',
        'longitude',
        'speed',
        'heading',
        'altitude',
        'status',
        'ignition_on',
        'odometer',
        'additional_data',
        'recorded_at',
    ];

    protected $casts = [
        'latitude' => 'decimal:8',
        'longitude' => 'decimal:8',
        'speed' => 'decimal:2',
        'heading' => 'decimal:2',
        'altitude' => 'decimal:2',
        'ignition_on' => 'boolean',
        'odometer' => 'decimal:2',
        'additional_data' => 'array',
        'recorded_at' => 'datetime',
    ];

    public const STATUS_MOVING = 'moving';
    public const STATUS_STOPPED = 'stopped';
    public const STATUS_IDLE = 'idle';

    public function vehicle()
    {
        return $this->belongsTo(TransportVehicle::class, 'vehicle_id');
    }

    public function route()
    {
        return $this->belongsTo(TransportRoute::class, 'route_id');
    }

    public function driver()
    {
        return $this->belongsTo(TransportDriver::class, 'driver_id');
    }

    public function scopeMoving($query)
    {
        return $query->where('status', self::STATUS_MOVING);
    }

    public function scopeStopped($query)
    {
        return $query->where('status', self::STATUS_STOPPED);
    }

    public function scopeIdle($query)
    {
        return $query->where('status', self::STATUS_IDLE);
    }

    public function scopeToday($query)
    {
        return $query->whereDate('recorded_at', now()->toDateString());
    }

    public function scopeByVehicle($query, $vehicleId)
    {
        return $query->where('vehicle_id', $vehicleId);
    }

    public function scopeRecent($query, $minutes = 30)
    {
        return $query->where('recorded_at', '>=', now()->subMinutes($minutes));
    }

    public function isMoving()
    {
        return $this->status === self::STATUS_MOVING;
    }

    public function isStopped()
    {
        return $this->status === self::STATUS_STOPPED;
    }

    public function getIdleDuration()
    {
        if (!$this->isStopped() && !$this->recorded_at) {
            return 0;
        }
        return now()->diffInMinutes($this->recorded_at);
    }
}
