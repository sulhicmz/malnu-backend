<?php

declare(strict_types=1);

namespace App\Models\Compliance;

use App\Models\Model;
use App\Models\User;

/**
 * DataBreachIncident Model.
 *
 * Tracks data breach incidents and response activities.
 */
class DataBreachIncident extends Model
{
    public $primaryKey = 'id';

    public $incrementing = false;

    protected $table = 'data_breach_incidents';

    protected $fillable = [
        'incident_type',
        'severity',
        'title',
        'description',
        'affected_records',
        'data_types_affected',
        'discovered_at',
        'reported_at',
        'reported_by',
        'assigned_to',
        'status',
        'root_cause',
        'mitigation_actions',
        'notification_sent',
        'notification_sent_at',
        'regulatory_report_required',
        'regulatory_report_submitted',
        'regulatory_submission_date',
    ];

    protected $casts = [
        'data_types_affected' => 'array',
        'discovered_at' => 'datetime',
        'reported_at' => 'datetime',
        'notification_sent' => 'boolean',
        'notification_sent_at' => 'datetime',
        'regulatory_report_required' => 'boolean',
        'regulatory_report_submitted' => 'boolean',
        'regulatory_submission_date' => 'date',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    protected $keyType = 'string';

    public function reportedBy()
    {
        return $this->belongsTo(User::class, 'reported_by', 'id');
    }

    public function assignedTo()
    {
        return $this->belongsTo(User::class, 'assigned_to', 'id');
    }

    public function scopeBySeverity($query, string $severity)
    {
        return $query->where('severity', $severity);
    }

    public function scopeByStatus($query, string $status)
    {
        return $query->where('status', $status);
    }

    public function scopeOpen($query)
    {
        return $query->where('status', 'open');
    }

    public function scopeInvestigating($query)
    {
        return $query->where('status', 'investigating');
    }

    public function scopeRequiresRegulatoryReport($query)
    {
        return $query->where('regulatory_report_required', true);
    }

    public function scopeNotReported($query)
    {
        return $query->where('regulatory_report_required', true)
            ->where('regulatory_report_submitted', false);
    }
}
