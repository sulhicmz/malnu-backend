<?php

declare(strict_types=1);

namespace App\Models\Behavior;

use App\Models\Model;
use App\Models\User;
use App\Traits\UsesUuid;

class DisciplineAction extends Model
{
    use UsesUuid;

    protected string $table = 'discipline_actions';

    protected array $fillable = [
        'incident_id',
        'assigned_to',
        'action_type',
        'action_type_other',
        'duration_days',
        'start_date',
        'end_date',
        'description',
        'conditions',
        'is_completed',
        'completed_at',
        'created_by',
        'updated_by',
    ];

    protected array $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'is_completed' => 'boolean',
        'completed_at' => 'datetime',
    ];

    public function scopeCompleted($query)
    {
        return $query->where('is_completed', true);
    }

    public function scopePending($query)
    {
        return $query->where('is_completed', false);
    }

    public function scopeByActionType($query, string $actionType)
    {
        return $query->where('action_type', $actionType);
    }

    public function scopeActive($query)
    {
        $now = date('Y-m-d');
        return $query->where('start_date', '<=', $now)
            ->where(function ($q) use ($now) {
                $q->where('end_date', '>=', $now)->orWhereNull('end_date');
            })
            ->where('is_completed', false);
    }

    public function incident()
    {
        return $this->belongsTo(BehaviorIncident::class, 'incident_id');
    }

    public function assignedTo()
    {
        return $this->belongsTo(User::class, 'assigned_to');
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
