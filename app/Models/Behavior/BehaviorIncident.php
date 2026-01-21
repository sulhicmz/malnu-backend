<?php

declare(strict_types=1);

namespace App\Models\Behavior;

use App\Models\Model;
use App\Models\SchoolManagement\Student;
use App\Models\User;
use App\Traits\UsesUuid;

class BehaviorIncident extends Model
{
    use UsesUuid;

    protected string $table = 'behavior_incidents';

    protected array $fillable = [
        'student_id',
        'reported_by',
        'behavior_category_id',
        'incident_date',
        'incident_time',
        'location',
        'severity',
        'description',
        'witnesses',
        'action_taken',
        'is_resolved',
        'resolved_by',
        'resolved_at',
        'resolution_notes',
        'parent_notified',
        'parent_notified_at',
        'created_by',
        'updated_by',
    ];

    protected array $casts = [
        'incident_date' => 'date',
        'incident_time' => 'datetime',
        'is_resolved' => 'boolean',
        'parent_notified' => 'boolean',
        'resolved_at' => 'datetime',
        'parent_notified_at' => 'datetime',
    ];

    public function scopeResolved($query)
    {
        return $query->where('is_resolved', true);
    }

    public function scopeUnresolved($query)
    {
        return $query->where('is_resolved', false);
    }

    public function scopeBySeverity($query, string $severity)
    {
        return $query->where('severity', $severity);
    }

    public function scopeByDateRange($query, string $startDate, string $endDate)
    {
        return $query->whereBetween('incident_date', [$startDate, $endDate]);
    }

    public function student()
    {
        return $this->belongsTo(Student::class, 'student_id');
    }

    public function reportedBy()
    {
        return $this->belongsTo(User::class, 'reported_by');
    }

    public function category()
    {
        return $this->belongsTo(BehaviorCategory::class, 'behavior_category_id');
    }

    public function resolvedBy()
    {
        return $this->belongsTo(User::class, 'resolved_by');
    }

    public function actions()
    {
        return $this->hasMany(DisciplineAction::class, 'incident_id');
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
