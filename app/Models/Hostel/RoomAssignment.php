<?php

declare(strict_types=1);

namespace App\Models\Hostel;

use App\Models\Model;
use App\Models\User;

class RoomAssignment extends Model
{
    protected $table = 'room_assignments';

    protected $fillable = [
        'student_id',
        'room_id',
        'hostel_id',
        'assignment_date',
        'checkout_date',
        'status',
        'bed_number',
        'notes',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'assignment_date' => 'date',
        'checkout_date' => 'date',
        'created_by' => 'string',
        'updated_by' => 'string',
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

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeInactive($query)
    {
        return $query->where('status', '!=', 'active');
    }

    public function scopeByHostel($query, $hostelId)
    {
        return $query->where('hostel_id', $hostelId);
    }

    public function scopeByStudent($query, $studentId)
    {
        return $query->where('student_id', $studentId);
    }

    public function getDurationAttribute()
    {
        if (!$this->checkout_date) {
            return $this->assignment_date->diffInDays(now());
        }
        return $this->assignment_date->diffInDays($this->checkout_date);
    }

    public function getIsActiveAttribute()
    {
        return $this->status === 'active';
    }
}
