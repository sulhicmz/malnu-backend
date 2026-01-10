<?php

declare(strict_types=1);

namespace App\Models\Behavior;

use App\Models\Model;

class DisciplineAction extends Model
{
    protected $table = 'discipline_actions';

    protected $fillable = [
        'incident_id',
        'assigned_by',
        'action_type',
        'description',
        'action_date',
        'status',
        'outcome',
    ];

    protected $casts = [
        'action_date' => 'date',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function incident()
    {
        return $this->belongsTo(Incident::class, 'incident_id');
    }

    public function assignedBy()
    {
        return $this->belongsTo(\App\Models\User::class, 'assigned_by');
    }

    public function scopeByIncident($query, $incidentId)
    {
        return $query->where('incident_id', $incidentId);
    }

    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }
}
