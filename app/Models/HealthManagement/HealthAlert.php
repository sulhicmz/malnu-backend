<?php

declare(strict_types=1);

namespace App\Models\HealthManagement;

use App\Models\Model;
use App\Models\SchoolManagement\Student;
use App\Models\User;

class HealthAlert extends Model
{
    protected $primaryKey = 'id';
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'student_id',
        'alert_type',
        'title',
        'message',
        'priority',
        'alert_date',
        'due_date',
        'status',
        'sent_date',
        'recipients',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'alert_date' => 'datetime',
        'due_date' => 'datetime',
        'sent_date' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updatedBy()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeSent($query)
    {
        return $query->where('status', 'sent');
    }

    public function scopeHighPriority($query)
    {
        return $query->where('priority', 'high');
    }

    public function scopeCritical($query)
    {
        return $query->where('priority', 'critical');
    }

    public function scopeOverdue($query)
    {
        return $query->where('status', 'pending')
                        ->where('due_date', '<', now());
    }

    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    public function isSent(): bool
    {
        return $this->status === 'sent';
    }

    public function isCritical(): bool
    {
        return $this->priority === 'critical';
    }

    public function isOverdue(): bool
    {
        return $this->status === 'pending' && 
               $this->due_date && 
               $this->due_date->isPast();
    }
}
