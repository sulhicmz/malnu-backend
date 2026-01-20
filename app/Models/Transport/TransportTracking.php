<?php

declare (strict_types = 1);

namespace App\Models\Transport;

use App\Traits\UsesUuid;
use Hyperf\DbConnection\Model\Model;

class TransportTracking extends Model
{
    use UsesUuid;

    protected string $table = 'transport_tracking';
    protected string $primaryKey = 'id';
    protected string $keyType = 'string';
    public bool $incrementing = false;

    protected array $fillable = [
        'vehicle_id',
        'route_id',
        'latitude',
        'longitude',
        'speed',
        'heading',
        'odometer',
        'recorded_at',
    ];

    protected array $casts = [
        'latitude' => 'decimal:7',
        'longitude' => 'decimal:7',
        'speed' => 'decimal:2',
        'heading' => 'decimal:2',
        'odometer' => 'decimal:2',
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

    public function scopeLatest($query)
    {
        return $query->orderBy('recorded_at', 'desc');
    }

    public function scopeActiveVehicles($query)
    {
        return $query->where('recorded_at', '>=', now()->subMinutes(5));
    }
}