<?php

declare(strict_types=1);

namespace App\Models;

class MedicalIncident extends Model
{
    public const SEVERITY_MILD = 'mild';

    public const SEVERITY_MODERATE = 'moderate';

    public const SEVERITY_SEVERE = 'severe';

    protected $fillable = [
        'student_id',
        'health_record_id',
        'incident_type',
        'description',
        'severity',
        'incident_date',
        'treatment_provided',
        'follow_up_actions',
        'parent_notified',
        'parent_notification_date',
        'notes',
        'reported_by',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'parent_notified' => 'boolean',
        'incident_date' => 'datetime',
        'parent_notification_date' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    public function student()
    {
        return $this->belongsTo(Student::class, 'student_id', 'id');
    }

    public function healthRecord()
    {
        return $this->belongsTo(HealthRecord::class, 'health_record_id', 'id');
    }

    public function reportedBy()
    {
        return $this->belongsTo(User::class, 'reported_by', 'id');
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by', 'id');
    }

    public function updatedBy()
    {
        return $this->belongsTo(User::class, 'updated_by', 'id');
    }

    public function scopeSevere($query)
    {
        return $query->where('severity', self::SEVERITY_SEVERE);
    }

    public function scopePendingParentNotification($query)
    {
        return $query->where('parent_notified', false);
    }

    public function scopeRecent($query, int $days = 30)
    {
        return $query->where('incident_date', '>=', now()->subDays($days));
    }

    public function getIsCriticalAttribute(): bool
    {
        return $this->severity === self::SEVERITY_SEVERE && ! $this->parent_notified;
    }
}
