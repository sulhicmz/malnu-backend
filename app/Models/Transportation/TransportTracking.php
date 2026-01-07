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
        'schedule_id',
        'latitude',
        'longitude',
        'speed',
        'direction',
        'status',
        'recorded_at',
    ];

    protected $casts = [
        'latitude' => 'decimal:7',
        'longitude' => 'decimal:7',
        'speed' => 'integer',
        'recorded_at' => 'datetime',
    ];

    public function vehicle()
    {
        return $this->belongsTo(TransportVehicle::class, 'vehicle_id');
    }

    public function route()
    {
        return $this->belongsTo(TransportRoute::class, 'route_id');
    }

    public function schedule()
    {
        return $this->belongsTo(TransportSchedule::class, 'schedule_id');
    }
}
