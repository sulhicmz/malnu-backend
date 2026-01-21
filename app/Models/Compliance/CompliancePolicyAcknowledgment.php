<?php

declare(strict_types=1);

namespace App\Models\Compliance;

use App\Models\Model;
use App\Models\User;

/**
 * CompliancePolicyAcknowledgment Model.
 *
 * Tracks when users acknowledge compliance policies.
 */
class CompliancePolicyAcknowledgment extends Model
{
    public $primaryKey = 'id';

    public $incrementing = false;

    protected $table = 'compliance_policy_acknowledgments';

    protected $fillable = [
        'policy_id',
        'user_id',
        'acknowledged_at',
        'acknowledgment_ip',
        'acknowledgment_device',
    ];

    protected $casts = [
        'acknowledged_at' => 'datetime',
        'created_at' => 'datetime',
    ];

    protected $keyType = 'string';

    public function policy()
    {
        return $this->belongsTo(CompliancePolicy::class, 'policy_id', 'id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }
}
