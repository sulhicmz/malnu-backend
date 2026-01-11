<?php

declare(strict_types=1);

namespace App\Models\Hostel;

use App\Models\Model;
use App\Models\User;

class BoardingAttendance extends Model
{
    protected $table = 'boarding_attendance';

    protected $fillable = [
        'student_id',
        'room_id',
        'hostel_id',
        'attendance_date',
        'check_in_time',
        'check_out_time',
        'status',
        'leave_type',
        'leave_start_date',
        'leave_end_date',
        'notes',
    ];

    protected $casts = [
        'attendance_date' => 'date',
        'check_in_time' => 'datetime',
        'check_out_time' => 'datetime',
        'leave_start_date' => 'date',
        'leave_end_date' => 'date',
    ];

    public function student()
    {
        return $this->belongsTo(User::class, 'student_id');
    }

    public function room()
    {
        return $this->belongsTo(Room::class, 'room_id');
    }

    public function hostel()
    {
        return $this->belongsTo(Hostel::class, 'hostel_id');
    }

    public function scopePresent($query)
    {
        return $query->where('status', 'present');
    }

    public function scopeAbsent($query)
    {
        return $query->where('status', 'absent');
    }

    public function scopeOnLeave($query)
    {
        return $query->where('status', 'on_leave');
    }

    public function scopeLate($query)
    {
        return $query->where('status', 'late');
    }

    public function scopeByDate($query, $date)
    {
        return $query->where('attendance_date', $date);
    }

    public function scopeByHostel($query, $hostelId)
    {
        return $query->where('hostel_id', $hostelId);
    }

    public function scopeByStudent($query, $studentId)
    {
        return $query->where('student_id', $studentId);
    }

    public function scopeDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('attendance_date', [$startDate, $endDate]);
    }

    public function getIsCheckedInAttribute()
    {
        return $this->check_in_time !== null && $this->check_out_time === null;
    }

    public function getIsOnLeaveAttribute()
    {
        return $this->status === 'on_leave';
    }

    public function getIsAbsentAttribute()
    {
        return $this->status === 'absent';
    }

    public function getTimeOnCampusAttribute()
    {
        if (!$this->check_in_time || !$this->check_out_time) {
            return null;
        }
        return $this->check_in_time->diffInMinutes($this->check_out_time);
    }
}
