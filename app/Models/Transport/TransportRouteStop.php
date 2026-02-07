<?php

declare (strict_types = 1);

namespace App\Models\Transport;

use App\Traits\UsesUuid;
use Hyperf\DbConnection\Model\Model;

class TransportRouteStop extends Model
{
    use UsesUuid;

    protected string $table = 'transport_route_stops';
    protected string $primaryKey = 'id';
    protected string $keyType = 'string';
    public bool $incrementing = false;

    protected array $fillable = [
        'route_id',
        'stop_id',
        'stop_order',
        'arrival_time',
        'departure_time',
        'fare',
    ];

    protected array $casts = [
        'fare' => 'decimal:2',
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