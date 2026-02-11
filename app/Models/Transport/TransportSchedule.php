<?php

declare (strict_types = 1);

namespace App\Models\Transport;

use App\Models\User;
use App\Traits\UsesUuid;
use Hyperf\DbConnection\Model\Model;

class TransportSchedule extends Model
{
    use UsesUuid;

    protected string $table = 'transport_schedules';
    protected string $primaryKey = 'id';
    protected string $keyType = 'string';
    public bool $incrementing = false;

    protected array $fillable = [
        'route_id',
        'vehicle_id',
        'driver_id',
        'day_of_week',
        'departure_time',
        'arrival_time',
        'is_active',
    ];

    protected array $casts = [
        'is_active' => 'boolean',
    ];

    public function route()
    {
        return $this->belongsTo(TransportRoute::class, 'route_id');
    }

    public function vehicle()
    {
        return $this->belongsTo(TransportVehicle::class, 'vehicle_id');
    }

    public function driver()
    {
        return $this->belongsTo(TransportDriver::class, 'driver_id');
    }

    public function isActive(): bool
    {
        return $this->is_active;
    }
}