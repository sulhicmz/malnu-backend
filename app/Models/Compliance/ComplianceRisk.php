<?php

declare(strict_types=1);

namespace App\Models\Compliance;

use App\Models\Model;
use App\Models\User;

/**
 * ComplianceRisk Model.
 *
 * Manages compliance risks identified in the system.
 */
class ComplianceRisk extends Model
{
    public $primaryKey = 'id';

    public $incrementing = false;

    protected $table = 'compliance_risks';

    protected $fillable = [
        'risk_title',
        'description',
        'risk_category',
        'likelihood',
        'impact',
        'risk_score',
        'affected_systems',
        'applicable_regulations',
        'mitigation_plan',
        'mitigation_status',
        'mitigation_priority',
        'target_mitigation_date',
        'actual_mitigation_date',
        'identified_by',
        'assigned_to',
        'status',
    ];

    protected $casts = [
        'risk_score' => 'integer',
        'affected_systems' => 'array',
        'applicable_regulations' => 'array',
        'target_mitigation_date' => 'date',
        'actual_mitigation_date' => 'date',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    protected $keyType = 'string';

    public function identifiedBy()
    {
        return $this->belongsTo(User::class, 'identified_by', 'id');
    }

    public function assignedTo()
    {
        return $this->belongsTo(User::class, 'assigned_to', 'id');
    }

    public function scopeByCategory($query, string $category)
    {
        return $query->where('risk_category', $category);
    }

    public function scopeByPriority($query, string $priority)
    {
        return $query->where('mitigation_priority', $priority);
    }

    public function scopeByStatus($query, string $status)
    {
        return $query->where('status', $status);
    }

    public function scopeByMitigationStatus($query, string $status)
    {
        return $query->where('mitigation_status', $status);
    }

    public function scopeOpen($query)
    {
        return $query->where('status', 'open');
    }

    public function scopeHighPriority($query)
    {
        return $query->whereIn('mitigation_priority', ['high', 'critical']);
    }

    public function scopeOverdue($query)
    {
        return $query->where('mitigation_status', '!=', 'completed')
            ->where('target_mitigation_date', '<', date('Y-m-d'));
    }

    public function calculateRiskScore(): void
    {
        $likelihoodScores = [
            'rare' => 1,
            'unlikely' => 2,
            'possible' => 3,
            'likely' => 4,
            'almost_certain' => 5,
        ];

        $impactScores = [
            'negligible' => 1,
            'minor' => 2,
            'moderate' => 3,
            'major' => 4,
            'catastrophic' => 5,
        ];

        $this->risk_score = ($likelihoodScores[$this->likelihood] ?? 1)
            * ($impactScores[$this->impact] ?? 1);
    }
}
