<?php

declare(strict_types=1);

namespace App\Models\Transportation;

use App\Models\SchoolManagement\Student;
use App\Models\User;
use App\Models\Model;

class TransportationRegistration extends Model
{
    protected $table = 'transportation_registrations';

    protected $fillable = [
        'student_id',
        'route_id',
        'stop_id',
        'pickup_location',
        'dropoff_location',
        'registration_date',
        'status',
        'emergency_contact',
        'notes',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'emergency_contact' => 'array',
        'created_by' => 'string',
        'updated_by' => 'string',
        'student_id' => 'string',
        'route_id' => 'string',
    ];

    public function student()
    {
        return $this->belongsTo(Student::class, 'student_id');
    }

    public function route()
    {
        return $this->belongsTo(TransportationRoute::class, 'route_id');
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
