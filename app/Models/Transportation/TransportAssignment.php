<?php

declare(strict_types=1);

namespace App\Models\Transportation;

use App\Models\User;
use App\Models\Model;

class TransportAssignment extends Model
{
    protected $table = 'transport_assignments';

    protected $fillable = [
        'route_id',
        'student_id',
        'pickup_stop_id',
        'dropoff_stop_id',
        'trip_type',
        'start_date',
        'end_date',
        'status',
        'fee_amount',
        'notes',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'fee_amount' => 'decimal:2',
        'created_by' => 'string',
        'updated_by' => 'string',
    ];

    public function route()
    {
        return $this->belongsTo(TransportRoute::class, 'route_id');
    }

    public function student()
    {
        return $this->belongsTo(User::class, 'student_id');
    }

    public function pickupStop()
    {
        return $this->belongsTo(TransportStop::class, 'pickup_stop_id');
    }

    public function dropoffStop()
    {
        return $this->belongsTo(TransportStop::class, 'dropoff_stop_id');
    }

    public function attendances()
    {
        return $this->hasMany(TransportAttendance::class, 'assignment_id');
    }

    public function fees()
    {
        return $this->hasMany(TransportFee::class, 'assignment_id');
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
