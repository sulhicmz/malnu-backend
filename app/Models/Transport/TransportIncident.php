<?php

declare (strict_types = 1);

namespace App\Models\Transport;

use App\Traits\UsesUuid;
use Hypervel\Database\Model\Model;

class TransportIncident extends Model
{
    use UsesUuid;

    protected string $table = 'transport_incidents';
    protected string $primaryKey = 'id';
    protected string $keyType = 'string';
    public bool $incrementing = false;

    protected array $fillable = [
        'vehicle_id',
        'route_id',
        'driver_id',
        'incident_type',
        'severity',
        'description',
        'incident_time',
        'location',
        'status',
        'resolution',
        'resolved_at',
    ];

    protected array $casts = [
        'incident_time' => 'datetime',
        'resolved_at' => 'datetime',
    ];

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

    public function isOpen(): bool
    {
        return $this->status === 'open';
    }

    public function isResolved(): bool
    {
        return $this->status === 'resolved';
    }

    public function isHighSeverity(): bool
    {
        return in_array($this->severity, ['high', 'critical']);
    }
}