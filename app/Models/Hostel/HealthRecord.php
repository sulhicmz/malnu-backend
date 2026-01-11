<?php

declare(strict_types=1);

namespace App\Models\Hostel;

use App\Models\Model;
use App\Models\User;

class HealthRecord extends Model
{
    protected $table = 'health_records';

    protected $fillable = [
        'student_id',
        'hostel_id',
        'record_type',
        'description',
        'checkup_date',
        'severity',
        'medication',
        'notes',
        'recorded_by',
    ];

    protected $casts = [
        'checkup_date' => 'date',
        'recorded_by' => 'string',
    ];

    public function student()
    {
        return $this->belongsTo(User::class, 'student_id');
    }

    public function hostel()
    {
        return $this->belongsTo(Hostel::class, 'hostel_id');
    }

    public function recordedBy()
    {
        return $this->belongsTo(User::class, 'recorded_by');
    }

    public function scopeByStudent($query, $studentId)
    {
        return $query->where('student_id', $studentId);
    }

    public function scopeByHostel($query, $hostelId)
    {
        return $query->where('hostel_id', $hostelId);
    }

    public function scopeByType($query, $type)
    {
        return $query->where('record_type', $type);
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
        return $query->where('checkup_date', '>=', now()->subDays($days));
    }

    public function scopeDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('checkup_date', [$startDate, $endDate]);
    }

    public function getIsCriticalAttribute()
    {
        return $this->severity === 'critical';
    }

    public function getRequiresMedicationAttribute()
    {
        return !empty($this->medication);
    }
}
