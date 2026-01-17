<?php

declare(strict_types=1);

namespace App\Models\Transportation;

use App\Models\Model;
use App\Models\Transportation\TransportationDriver;
use App\Models\Transportation\TransportationVehicle;
use App\Models\Transportation\TransportationRegistration;

class TransportationIncident extends Model
{
    protected $primaryKey = 'id';
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'driver_id',
        'vehicle_id',
        'registration_id',
        'incident_date',
        'incident_time',
        'incident_type',
        'description',
        'severity',
        'status',
        'action_taken',
        'witnesses',
        'reported_by',
    ];

    protected $casts = [
        'incident_date' => 'date',
        'incident_time' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function driver()
    {
        return $this->belongsTo(TransportationDriver::class);
    }

    public function vehicle()
    {
        return $this->belongsTo(TransportationVehicle::class);
    }

    public function registration()
    {
        return $this->belongsTo(TransportationRegistration::class);
    }

    public function reporter()
    {
        return $this->belongsTo('App\Models\User');
    }

    public function scopeOpen($query)
    {
        return $query->where('status', 'reported');
    }

    public function scopeResolved($query)
    {
        return $query->where('status', 'resolved');
    }

    public function scopeCritical($query)
    {
        return $query->where('severity', 'critical');
    }
}
