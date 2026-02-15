<?php

declare (strict_types = 1);

namespace App\Models\Transport;

use App\Traits\UsesUuid;
use Hypervel\Database\Model\Model;

class TransportRoute extends Model
{
    use UsesUuid;

    protected string $table = 'transport_routes';
    protected string $primaryKey = 'id';
    protected string $keyType = 'string';
    public bool $incrementing = false;

    protected array $fillable = [
        'name',
        'code',
        'description',
        'start_time',
        'end_time',
        'capacity',
        'status',
        'is_active',
    ];

    protected array $casts = [
        'is_active' => 'boolean',
    ];

    public function stops()
    {
        return $this->belongsToMany(TransportStop::class, 'transport_route_stops')
            ->withPivot(['stop_order', 'arrival_time', 'departure_time', 'fare'])
            ->orderBy('stop_order');
    }

    public function routeStops()
    {
        return $this->hasMany(TransportRouteStop::class, 'route_id')->orderBy('stop_order');
    }

    public function assignments()
    {
        return $this->hasMany(TransportAssignment::class, 'route_id');
    }

    public function schedules()
    {
        return $this->hasMany(TransportSchedule::class, 'route_id');
    }

    public function attendance()
    {
        return $this->hasMany(TransportAttendance::class, 'route_id');
    }

    public function incidents()
    {
        return $this->hasMany(TransportIncident::class, 'route_id');
    }

    public function trackingRecords()
    {
        return $this->hasMany(TransportTracking::class, 'route_id');
    }

    public function isActive(): bool
    {
        return $this->is_active && $this->status === 'active';
    }

    public function getCapacity(): int
    {
        return $this->assignments()->where('is_active', true)->count();
    }

    public function isAtCapacity(): bool
    {
        return $this->getCapacity() >= $this->capacity;
    }
}