<?php

declare(strict_types=1);

namespace App\Models\Transportation;

use App\Models\User;
use App\Models\Model;

class TransportAttendance extends Model
{
    protected $table = 'transport_attendance';

    protected $fillable = [
        'assignment_id',
        'student_id',
        'route_id',
        'trip_type',
        'attendance_date',
        'pickup_time',
        'dropoff_time',
        'pickup_status',
        'dropoff_status',
        'notes',
        'recorded_by',
    ];

    protected $casts = [
        'attendance_date' => 'date',
        'pickup_time' => 'datetime',
        'dropoff_time' => 'datetime',
        'recorded_by' => 'string',
    ];

    public function assignment()
    {
        return $this->belongsTo(TransportAssignment::class, 'assignment_id');
    }

    public function student()
    {
        return $this->belongsTo(User::class, 'student_id');
    }

    public function route()
    {
        return $this->belongsTo(TransportRoute::class, 'route_id');
    }

    public function recordedBy()
    {
        return $this->belongsTo(User::class, 'recorded_by');
    }
}
