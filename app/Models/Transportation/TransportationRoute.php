<?php

declare(strict_types=1);

namespace App\Models\Transportation;

use App\Models\User;
use App\Models\SchoolManagement\Student;
use App\Models\Model;

class TransportationRoute extends Model
{
    protected $table = 'transportation_routes';

    protected $fillable = [
        'route_name',
        'route_description',
        'start_location',
        'end_location',
        'status',
        'start_time',
        'end_time',
        'distance_km',
        'fuel_capacity',
        'vehicle_id',
        'driver_id',
        'stops',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'stops' => 'array',
        'created_by' => 'string',
        'updated_by' => 'string',
        'vehicle_id' => 'string',
        'driver_id' => 'string',
    ];

    public function vehicle()
    {
        return $this->belongsTo(TransportationVehicle::class, 'vehicle_id');
    }

    public function driver()
    {
        return $this->belongsTo(User::class, 'driver_id');
    }

    public function registrations()
    {
        return $this->hasMany(TransportationRegistration::class, 'route_id');
    }

    public function assignments()
    {
        return $this->hasMany(TransportationAssignment::class, 'route_id');
    }

    public function fees()
    {
        return $this->hasMany(TransportationFee::class, 'route_id');
    }

    public function incidents()
    {
        return $this->hasMany(TransportationIncident::class, 'route_id');
    }
}
