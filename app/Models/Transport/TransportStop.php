<?php

declare (strict_types = 1);

namespace App\Models\Transport;

use App\Traits\UsesUuid;
use Hypervel\Database\Model\Model;

class TransportStop extends Model
{
    use UsesUuid;

    protected string $table = 'transport_stops';
    protected string $primaryKey = 'id';
    protected string $keyType = 'string';
    public bool $incrementing = false;

    protected array $fillable = [
        'name',
        'address',
        'latitude',
        'longitude',
        'type',
        'is_active',
    ];

    protected array $casts = [
        'is_active' => 'boolean',
        'latitude' => 'decimal:7',
        'longitude' => 'decimal:7',
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

    public function distanceTo(float $latitude, float $longitude): float
    {
        $earthRadius = 6371;

        $latFrom = deg2rad($this->latitude);
        $lonFrom = deg2rad($this->longitude);
        $latTo = deg2rad($latitude);
        $lonTo = deg2rad($longitude);

        $latDelta = $latTo - $latFrom;
        $lonDelta = $lonTo - $lonFrom;

        $angle = 2 * asin(sqrt(pow(sin($latDelta / 2), 2) +
            cos($latFrom) * cos($latTo) * pow(sin($lonDelta / 2), 2)));

        return $angle * $earthRadius;
    }
}