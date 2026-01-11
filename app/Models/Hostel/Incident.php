<?php

declare(strict_types=1);

namespace App\Models\Hostel;

use App\Models\Model;
use App\Models\User;

class Incident extends Model
{
    protected $table = 'incidents';

    protected $fillable = [
        'hostel_id',
        'student_id',
        'room_id',
        'incident_type',
        'severity',
        'description',
        'incident_date',
        'status',
        'action_taken',
        'disciplinary_action',
        'reported_by',
        'resolved_by',
        'resolved_at',
    ];

    protected $casts = [
        'incident_date' => 'datetime',
        'resolved_at' => 'date',
        'reported_by' => 'string',
        'resolved_by' => 'string',
    ];

    public function hostel()
    {
        return $this->belongsTo(Hostel::class, 'hostel_id');
    }

    public function student()
    {
        return $this->belongsTo(User::class, 'student_id');
    }

    public function room()
    {
        return $this->belongsTo(Room::class, 'room_id');
    }

    public function reportedBy()
    {
        return $this->belongsTo(User::class, 'reported_by');
    }

    public function resolvedBy()
    {
        return $this->belongsTo(User::class, 'resolved_by');
    }

    public function scopeOpen($query)
    {
        return $query->where('status', 'open');
    }

    public function scopeInProgress($query)
    {
        return $query->where('status', 'in_progress');
    }

    public function scopeResolved($query)
    {
        return $query->where('status', 'resolved');
    }

    public function scopeByHostel($query, $hostelId)
    {
        return $query->where('hostel_id', $hostelId);
    }

    public function scopeByStudent($query, $studentId)
    {
        return $query->where('student_id', $studentId);
    }

    public function scopeByType($query, $type)
    {
        return $query->where('incident_type', $type);
    }

    public function scopeBySeverity($query, $severity)
    {
        return $query->where('severity', $severity);
    }

    public function scopeCritical($query)
    {
        return $query->where('severity', 'critical');
    }

    public function scopeRecent($query, $days = 30)
    {
        return $query->where('incident_date', '>=', now()->subDays($days));
    }

    public function scopeDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('incident_date', [$startDate, $endDate]);
    }

    public function getIsResolvedAttribute()
    {
        return $this->status === 'resolved';
    }

    public function getIsCriticalAttribute()
    {
        return $this->severity === 'critical';
    }

    public function getHasDisciplinaryActionAttribute()
    {
        return !empty($this->disciplinary_action);
    }

    public function getResolutionTimeAttribute()
    {
        if (!$this->resolved_at || !$this->incident_date) {
            return null;
        }
        return $this->incident_date->diffInDays($this->resolved_at);
    }
}
