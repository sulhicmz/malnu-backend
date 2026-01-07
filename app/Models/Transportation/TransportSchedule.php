<?php

declare(strict_types=1);

namespace App\Models\Transportation;

use App\Models\User;
use App\Models\Model;

class TransportSchedule extends Model
{
    protected $table = 'transport_schedules';

    protected $fillable = [
        'route_id',
        'vehicle_id',
        'driver_id',
        'shift',
        'day_type',
        'status',
        'effective_start_date',
        'effective_end_date',
        'metadata',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'effective_start_date' => 'date',
        'effective_end_date' => 'date',
        'metadata' => 'array',
        'created_by' => 'string',
        'updated_by' => 'string',
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

    public function trackingRecords()
    {
        return $this->hasMany(TransportTracking::class, 'schedule_id');
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
