<?php

declare(strict_types=1);

namespace App\Models\Transportation;

use App\Models\Model;

class TransportRouteStop extends Model
{
    protected $table = 'transport_route_stops';

    protected $fillable = [
        'route_id',
        'stop_id',
        'sequence_order',
        'pickup_time',
        'dropoff_time',
        'distance_from_start',
        'estimated_duration',
    ];

    protected $casts = [
        'pickup_time' => 'datetime',
        'dropoff_time' => 'datetime',
        'distance_from_start' => 'decimal:2',
        'estimated_duration' => 'integer',
    ];

    public function route()
    {
        return $this->belongsTo(TransportRoute::class, 'route_id');
    }

    public function stop()
    {
        return $this->belongsTo(TransportStop::class, 'stop_id');
    }
}
