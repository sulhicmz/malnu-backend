<?php

declare(strict_types=1);

namespace App\Models\HealthManagement;

use App\Models\Model;
use App\Models\SchoolManagement\Student;
use App\Models\User;

class MedicalIncident extends Model
{
    protected $primaryKey = 'id';
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'student_id',
        'incident_date',
        'incident_type',
        'description',
        'injury_details',
        'severity',
        'treatment_provided',
        'reported_by',
        'treated_by',
        'follow_up_actions',
        'follow_up_date',
        'parent_notified',
        'parent_notification_date',
        'location',
        'status',
        'notes',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'incident_date' => 'datetime',
        'follow_up_date' => 'date',
        'parent_notified' => 'boolean',
        'parent_notification_date' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function reportedBy()
    {
        return $this->belongsTo(User::class, 'reported_by');
    }

    public function treatedBy()
    {
        return $this->belongsTo(User::class, 'treated_by');
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updatedBy()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function scopeSevere($query)
    {
        return $query->whereIn('severity', ['severe', 'life_threatening']);
    }

    public function scopeOpen($query)
    {
        return $query->where('status', 'open');
    }

    public function scopeResolved($query)
    {
        return $query->where('status', 'resolved');
    }

    public function isSevere(): bool
    {
        return in_array($this->severity, ['severe', 'life_threatening']);
    }

    public function isOpen(): bool
    {
        return $this->status === 'open';
    }

    public function isResolved(): bool
    {
        return $this->status === 'resolved';
    }
}
