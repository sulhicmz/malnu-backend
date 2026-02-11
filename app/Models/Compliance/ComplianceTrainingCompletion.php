<?php

declare(strict_types=1);

namespace App\Models\Compliance;

use App\Models\Model;
use App\Models\User;

/**
 * ComplianceTrainingCompletion Model.
 *
 * Tracks completion of compliance training by users.
 */
class ComplianceTrainingCompletion extends Model
{
    public $primaryKey = 'id';

    public $incrementing = false;

    protected $table = 'compliance_training_completions';

    protected $fillable = [
        'training_id',
        'user_id',
        'completed_at',
        'score',
        'passed',
        'completion_ip',
        'completion_device',
    ];

    protected $casts = [
        'score' => 'decimal:2',
        'passed' => 'boolean',
        'completed_at' => 'datetime',
        'created_at' => 'datetime',
    ];

    protected $keyType = 'string';

    public function training()
    {
        return $this->belongsTo(ComplianceTraining::class, 'training_id', 'id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }
}
