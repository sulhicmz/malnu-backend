<?php

declare(strict_types=1);

namespace App\Models\Compliance;

use App\Models\Model;
use App\Models\User;

/**
 * ComplianceAudit Model.
 *
 * Tracks compliance-relevant activities for audit trail.
 * Logged automatically via middleware or manual calls.
 */
class ComplianceAudit extends Model
{
    public $primaryKey = 'id';

    public $incrementing = false;

    protected $table = 'compliance_audits';

    protected $fillable = [
        'user_id',
        'action_type',
        'entity_type',
        'entity_id',
        'description',
        'old_values',
        'new_values',
        'ip_address',
        'user_agent',
        'request_method',
        'request_path',
        'compliance_tags',
        'severity',
    ];

    protected $casts = [
        'old_values' => 'array',
        'new_values' => 'array',
        'compliance_tags' => 'array',
        'created_at' => 'datetime',
    ];

    protected $keyType = 'string';

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    public function scopeByActionType($query, string $actionType)
    {
        return $query->where('action_type', $actionType);
    }

    public function scopeByEntityType($query, string $entityType)
    {
        return $query->where('entity_type', $entityType);
    }

    public function scopeBySeverity($query, string $severity)
    {
        return $query->where('severity', $severity);
    }

    public function scopeByTag($query, string $tag)
    {
        return $query->whereJsonContains('compliance_tags', $tag);
    }

    public function scopeRecent($query, int $days = 30)
    {
        return $query->where('created_at', '>=', now()->subDays($days));
    }
}
