<?php

declare(strict_types=1);

namespace App\Models\Transportation;

use App\Models\User;
use App\Models\Model;

class TransportationVehicle extends Model
{
    protected $table = 'transportation_vehicles';

    protected $fillable = [
        'vehicle_number',
        'license_plate',
        'vehicle_type',
        'capacity',
        'model',
        'make',
        'year',
        'status',
        'fuel_consumption',
        'last_maintenance_date',
        'next_maintenance_date',
        'notes',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'year' => 'integer',
        'created_by' => 'string',
        'updated_by' => 'string',
    ];

    public function routes()
    {
        return $this->hasMany(TransportationRoute::class, 'vehicle_id');
    }

    public function incidents()
    {
        return $this->hasMany(TransportationIncident::class, 'vehicle_id');
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
