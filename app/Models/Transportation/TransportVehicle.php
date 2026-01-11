<?php

declare(strict_types=1);

namespace App\Models\Transportation;

use App\Models\User;
use App\Models\Model;

class TransportVehicle extends Model
{
    protected $table = 'transport_vehicles';

    protected $fillable = [
        'vehicle_number',
        'license_plate',
        'type',
        'capacity',
        'make',
        'model',
        'year',
        'color',
        'status',
        'description',
        'insurance_expiry',
        'registration_expiry',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'capacity' => 'integer',
        'year' => 'integer',
        'insurance_expiry' => 'date',
        'registration_expiry' => 'date',
        'created_by' => 'string',
        'updated_by' => 'string',
    ];

    public function schedules()
    {
        return $this->hasMany(TransportSchedule::class, 'vehicle_id');
    }

    public function trackingRecords()
    {
        return $this->hasMany(TransportTracking::class, 'vehicle_id');
    }

    public function notifications()
    {
        return $this->hasMany(TransportNotification::class, 'vehicle_id');
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
