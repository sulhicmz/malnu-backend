<?php

namespace App\Models;

use Hyperf\DbConnection\Model\Model;
use Hyperf\Database\Model\Events\Restoring;
use Hyperf\Database\Model\Events\Restored;

class ComplianceRequirement extends Model
{
    protected $table = 'compliance_requirements';

    protected $fillable = [
        'name',
        'description',
        'category',
        'regulatory_body',
        'status',
        'due_date',
        'completion_date',
        'responsible_staff_id',
        'priority',
        'notes',
        'document_path',
    ];

    protected $casts = [
        'due_date' => 'date',
        'completion_date' => 'date',
    ];

    public function responsibleStaff()
    {
        return $this->belongsTo(Staff::class, 'responsible_staff_id');
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeInProgress($query)
    {
        return $query->where('status', 'in_progress');
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    public function scopeOverdue($query)
    {
        return $query->where('status', '!=', 'completed')
            ->where('due_date', '<', now());
    }

    public function scopeHighPriority($query)
    {
        return $query->where('priority', 'high');
    }

    public function isOverdue(): bool
    {
        return $this->status !== 'completed' && $this->due_date && $this->due_date->isPast();
    }
}
