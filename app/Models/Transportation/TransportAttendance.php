<?php

declare(strict_types=1);

namespace App\Models\Transportation;

use App\Models\Student;
use App\Models\User;
use App\Models\Model;

class TransportAttendance extends Model
{
    protected $table = 'transport_attendance';

    protected $fillable = [
        'assignment_id',
        'student_id',
        'attendance_date',
        'session_type',
        'boarding_status',
        'boarding_time',
        'alighting_time',
        'boarding_stop_id',
        'alighting_stop_id',
        'notes',
        'parent_notified',
        'notification_time',
        'recorded_by',
    ];

    protected $casts = [
        'attendance_date' => 'date',
        'boarding_time' => 'datetime:H:i:s',
        'alighting_time' => 'datetime:H:i:s',
        'parent_notified' => 'boolean',
        'notification_time' => 'datetime',
        'recorded_by' => 'string',
    ];

    public const STATUS_PENDING = 'pending';
    public const STATUS_BOARDED = 'boarded';
    public const STATUS_MISSED = 'missed';
    public const STATUS_EXCUSED = 'excused';

    public const SESSION_MORNING = 'morning';
    public const SESSION_AFTERNOON = 'afternoon';

    public function recordedBy()
    {
        return $this->belongsTo(User::class, 'recorded_by');
    }

    public function assignment()
    {
        return $this->belongsTo(TransportAssignment::class, 'assignment_id');
    }

    public function student()
    {
        return $this->belongsTo(Student::class, 'student_id');
    }

    public function boardingStop()
    {
        return $this->belongsTo(TransportStop::class, 'boarding_stop_id');
    }

    public function alightingStop()
    {
        return $this->belongsTo(TransportStop::class, 'alighting_stop_id');
    }

    public function scopeToday($query)
    {
        return $query->whereDate('attendance_date', now()->toDateString());
    }

    public function scopeByDate($query, $date)
    {
        return $query->whereDate('attendance_date', $date);
    }

    public function scopeBoarded($query)
    {
        return $query->where('boarding_status', self::STATUS_BOARDED);
    }

    public function scopeMissed($query)
    {
        return $query->where('boarding_status', self::STATUS_MISSED);
    }

    public function scopeExcused($query)
    {
        return $query->where('boarding_status', self::STATUS_EXCUSED);
    }

    public function scopeMorning($query)
    {
        return $query->where('session_type', self::SESSION_MORNING);
    }

    public function scopeAfternoon($query)
    {
        return $query->where('session_type', self::SESSION_AFTERNOON);
    }

    public function isBoarded()
    {
        return $this->boarding_status === self::STATUS_BOARDED;
    }

    public function isMissed()
    {
        return $this->boarding_status === self::STATUS_MISSED;
    }

    public function isExcused()
    {
        return $this->boarding_status === self::STATUS_EXCUSED;
    }
}
