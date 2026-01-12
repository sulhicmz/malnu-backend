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
        'day_of_week',
        'session_type',
        'departure_time',
        'first_stop_arrival',
        'last_stop_arrival',
        'status',
        'schedule_notes',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'departure_time' => 'datetime:H:i:s',
        'first_stop_arrival' => 'datetime:H:i:s',
        'last_stop_arrival' => 'datetime:H:i:s',
        'schedule_notes' => 'array',
        'created_by' => 'string',
        'updated_by' => 'string',
    ];

    public const STATUS_ACTIVE = 'active';
    public const STATUS_INACTIVE = 'inactive';

    public const SESSION_MORNING = 'morning';
    public const SESSION_AFTERNOON = 'afternoon';

    public const DAYS = [
        'monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'
    ];

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updatedBy()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

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

    public function scopeActive($query)
    {
        return $query->where('status', self::STATUS_ACTIVE);
    }

    public function scopeToday($query)
    {
        $today = strtolower(now()->format('l'));
        return $query->where('day_of_week', $today);
    }

    public function scopeMorning($query)
    {
        return $query->where('session_type', self::SESSION_MORNING);
    }

    public function scopeAfternoon($query)
    {
        return $query->where('session_type', self::SESSION_AFTERNOON);
    }
}
