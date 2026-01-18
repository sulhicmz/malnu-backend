<?php

declare(strict_types=1);

namespace App\Models\Transportation;

use App\Models\SchoolManagement\Student;
use App\Models\User;
use App\Models\Model;

class TransportationIncident extends Model
{
    protected $table = 'transportation_incidents';

    protected $fillable = [
        'route_id',
        'vehicle_id',
        'driver_id',
        'student_id',
        'incident_type',
        'incident_date',
        'description',
        'severity',
        'status',
        'resolution',
        'resolved_date',
        'reported_by',
        'notes',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'created_by' => 'string',
        'updated_by' => 'string',
        'route_id' => 'string',
        'vehicle_id' => 'string',
        'driver_id' => 'string',
        'student_id' => 'string',
        'incident_date' => 'datetime',
        'resolved_date' => 'datetime',
        'reported_by' => 'array',
    ];

    public function route()
    {
        return $this->belongsTo(TransportationRoute::class, 'route_id');
    }

    public function vehicle()
    {
        return $this->belongsTo(TransportationVehicle::class, 'vehicle_id');
    }

    public function driver()
    {
        return $this->belongsTo(User::class, 'driver_id');
    }

    public function student()
    {
        return $this->belongsTo(Student::class, 'student_id');
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
