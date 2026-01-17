<?php

declare(strict_types=1);

namespace App\Models\Transportation;

use App\Models\Model;
use App\Models\Transportation\TransportationRegistration;

class TransportationAssignment extends Model
{
    protected $primaryKey = 'id';
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'registration_id',
        'driver_id',
        'vehicle_id',
        'assignment_date',
        'assignment_end',
        'assignment_type',
        'notes',
    ];

    protected $casts = [
        'assignment_date' => 'date',
        'assignment_end' => 'date',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function registration()
    {
        return $this->belongsTo(TransportationRegistration::class);
    }

    public function driver()
    {
        return $this->belongsTo(TransportationDriver::class);
    }

    public function vehicle()
    {
        return $this->belongsTo('App\Models\Transportation\TransportationVehicle');
    }
}
