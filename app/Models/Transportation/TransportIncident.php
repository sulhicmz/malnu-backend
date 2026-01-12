<?php

declare(strict_types=1);

namespace App\Models\Transportation;

use App\Models\User;
use App\Models\Model;

class TransportIncident extends Model
{
    protected $table = 'transport_incidents';

    protected $fillable = [
        'vehicle_id',
        'driver_id',
        'route_id',
        'incident_type',
        'severity',
        'incident_time',
        'description',
        'latitude',
        'longitude',
        'location_address',
        'actions_taken',
        'follow_up_required',
        'parent_notified',
        'notification_time',
        'police_reported',
        'police_report_number',
        'students_involved',
        'student_ids',
        'evidence_photos',
        'status',
        'reported_by',
        'resolved_by',
        'resolved_at',
        'resolution_details',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'incident_time' => 'datetime',
        'latitude' => 'decimal:8',
        'longitude' => 'decimal:8',
        'parent_notified' => 'boolean',
        'notification_time' => 'datetime',
        'police_reported' => 'boolean',
        'students_involved' => 'integer',
        'student_ids' => 'array',
        'evidence_photos' => 'array',
        'resolved_at' => 'datetime',
        'resolution_details' => 'array',
        'reported_by' => 'string',
        'resolved_by' => 'string',
        'created_by' => 'string',
        'updated_by' => 'string',
    ];

    public const TYPE_ACCIDENT = 'accident';
    public const TYPE_BREAKDOWN = 'breakdown';
    public const TYPE_DELAY = 'delay';
    public const TYPE_MEDICAL = 'medical';
    public const TYPE_SAFETY_ISSUE = 'safety_issue';

    public const SEVERITY_MINOR = 'minor';
    public const SEVERITY_MODERATE = 'moderate';
    public const SEVERITY_MAJOR = 'major';
    public const SEVERITY_CRITICAL = 'critical';

    public const STATUS_OPEN = 'open';
    public const STATUS_UNDER_INVESTIGATION = 'under_investigation';
    public const STATUS_RESOLVED = 'resolved';
    public const STATUS_CLOSED = 'closed';

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updatedBy()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function reportedBy()
    {
        return $this->belongsTo(User::class, 'reported_by');
    }

    public function resolvedBy()
    {
        return $this->belongsTo(User::class, 'resolved_by');
    }

    public function vehicle()
    {
        return $this->belongsTo(TransportVehicle::class, 'vehicle_id');
    }

    public function driver()
    {
        return $this->belongsTo(TransportDriver::class, 'driver_id');
    }

    public function route()
    {
        return $this->belongsTo(TransportRoute::class, 'route_id');
    }

    public function scopeOpen($query)
    {
        return $query->where('status', self::STATUS_OPEN);
    }

    public function scopeResolved($query)
    {
        return $query->where('status', self::STATUS_RESOLVED);
    }

    public function scopeCritical($query)
    {
        return $query->where('severity', self::SEVERITY_CRITICAL);
    }

    public function scopeByType($query, $type)
    {
        return $query->where('incident_type', $type);
    }

    public function isOpen()
    {
        return in_array($this->status, [self::STATUS_OPEN, self::STATUS_UNDER_INVESTIGATION]);
    }

    public function isResolved()
    {
        return $this->status === self::STATUS_RESOLVED || $this->status === self::STATUS_CLOSED;
    }

    public function isCritical()
    {
        return $this->severity === self::SEVERITY_CRITICAL || $this->severity === self::SEVERITY_MAJOR;
    }
}
