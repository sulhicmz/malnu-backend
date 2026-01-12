<?php

declare(strict_types=1);

namespace App\Models\Transportation;

use App\Models\Student;
use App\Models\User;
use App\Models\Model;

class TransportAssignment extends Model
{
    protected $table = 'transport_assignments';

    protected $fillable = [
        'student_id',
        'route_id',
        'stop_id',
        'vehicle_id',
        'driver_id',
        'effective_date',
        'end_date',
        'status',
        'session_type',
        'fee_status',
        'monthly_fee',
        'additional_info',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'effective_date' => 'date',
        'end_date' => 'date',
        'monthly_fee' => 'decimal:2',
        'additional_info' => 'array',
        'created_by' => 'string',
        'updated_by' => 'string',
    ];

    public const STATUS_ACTIVE = 'active';
    public const STATUS_INACTIVE = 'inactive';
    public const STATUS_CANCELLED = 'cancelled';

    public const SESSION_MORNING = 'morning';
    public const SESSION_AFTERNOON = 'afternoon';
    public const SESSION_BOTH = 'both';

    public const FEE_PENDING = 'pending';
    public const FEE_PAID = 'paid';
    public const FEE_EXEMPT = 'exempt';

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updatedBy()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function student()
    {
        return $this->belongsTo(Student::class, 'student_id');
    }

    public function route()
    {
        return $this->belongsTo(TransportRoute::class, 'route_id');
    }

    public function stop()
    {
        return $this->belongsTo(TransportStop::class, 'stop_id');
    }

    public function vehicle()
    {
        return $this->belongsTo(TransportVehicle::class, 'vehicle_id');
    }

    public function driver()
    {
        return $this->belongsTo(TransportDriver::class, 'driver_id');
    }

    public function attendance()
    {
        return $this->hasMany(TransportAttendance::class, 'assignment_id');
    }

    public function scopeActive($query)
    {
        return $query->where('status', self::STATUS_ACTIVE)
            ->whereNull('end_date')
            ->orWhere('end_date', '>', now());
    }

    public function scopeByStudent($query, $studentId)
    {
        return $query->where('student_id', $studentId);
    }

    public function scopeByRoute($query, $routeId)
    {
        return $query->where('route_id', $routeId);
    }

    public function isActive()
    {
        return $this->status === self::STATUS_ACTIVE &&
            (!$this->end_date || $this->end_date->isFuture());
    }

    public function usesMorningSession()
    {
        return $this->session_type === self::SESSION_MORNING || $this->session_type === self::SESSION_BOTH;
    }

    public function usesAfternoonSession()
    {
        return $this->session_type === self::SESSION_AFTERNOON || $this->session_type === self::SESSION_BOTH;
    }
}
