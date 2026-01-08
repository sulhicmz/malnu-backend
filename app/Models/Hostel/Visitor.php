<?php

declare(strict_types=1);

namespace App\Models\Hostel;

use App\Models\Model;
use App\Models\User;

class Visitor extends Model
{
    protected $table = 'visitors';

    protected $fillable = [
        'hostel_id',
        'visitor_student_id',
        'visitor_name',
        'visitor_phone',
        'relationship',
        'id_proof_type',
        'id_proof_number',
        'purpose',
        'visit_date',
        'check_in_time',
        'check_out_time',
        'status',
        'approved_by',
        'notes',
    ];

    protected $casts = [
        'visit_date' => 'datetime',
        'check_in_time' => 'datetime',
        'check_out_time' => 'datetime',
        'approved_by' => 'string',
    ];

    public function hostel()
    {
        return $this->belongsTo(Hostel::class, 'hostel_id');
    }

    public function visitorStudent()
    {
        return $this->belongsTo(User::class, 'visitor_student_id');
    }

    public function approvedBy()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    public function scopeRejected($query)
    {
        return $query->where('status', 'rejected');
    }

    public function scopeCheckedIn($query)
    {
        return $query->where('status', 'checked_in');
    }

    public function scopeCheckedOut($query)
    {
        return $query->where('status', 'checked_out');
    }

    public function scopeByHostel($query, $hostelId)
    {
        return $query->where('hostel_id', $hostelId);
    }

    public function scopeByDate($query, $date)
    {
        return $query->whereDate('visit_date', $date);
    }

    public function getVisitDurationAttribute()
    {
        if (!$this->check_in_time || !$this->check_out_time) {
            return null;
        }
        return $this->check_in_time->diffInMinutes($this->check_out_time);
    }

    public function getIsOnSiteAttribute()
    {
        return $this->status === 'checked_in';
    }
}
