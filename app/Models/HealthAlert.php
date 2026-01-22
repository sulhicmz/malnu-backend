<?php

declare(strict_types=1);

namespace App\Models;

class HealthAlert extends Model
{
    protected string $primaryKey = 'id';
    protected string $keyType = 'string';
    public bool $incrementing = false;

    const STATUS_PENDING = 'pending';
    const STATUS_SENT = 'sent';
    const STATUS_ACKNOWLEDGED = 'acknowledged';
    const STATUS_RESOLVED = 'resolved';

    const PRIORITY_LOW = 'low';
    const PRIORITY_MEDIUM = 'medium';
    const PRIORITY_HIGH = 'high';
    const PRIORITY_CRITICAL = 'critical';

    protected $fillable = [
        'student_id',
        'health_record_id',
        'alert_type',
        'priority',
        'message',
        'due_date',
        'status',
        'sent_date',
        'acknowledged_date',
        'resolved_date',
        'notes',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'due_date' => 'date',
        'sent_date' => 'datetime',
        'acknowledged_date' => 'datetime',
        'resolved_date' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    public function student()
    {
        return $this->belongsTo(Student::class, 'student_id', 'id');
    }

    public function healthRecord()
    {
        return $this->belongsTo(HealthRecord::class, 'health_record_id', 'id');
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by', 'id');
    }

    public function updatedBy()
    {
        return $this->belongsTo(User::class, 'updated_by', 'id');
    }

    public function scopeByType($query, string $type)
    {
        return $query->where('alert_type', $type);
    }

    public function scopeByPriority($query, string $priority)
    {
        return $query->where('priority', $priority);
    }

    public function scopeHighPriority($query)
    {
        return $query->whereIn('priority', [self::PRIORITY_HIGH, self::PRIORITY_CRITICAL]);
    }

    public function scopePending($query)
    {
        return $query->where('status', self::STATUS_PENDING);
    }

    public function scopeOverdue($query)
    {
        return $query->where('status', self::STATUS_PENDING)
                      ->whereNotNull('due_date')
                      ->where('due_date', '<', now()->format('Y-m-d'));
    }

    public function scopeSent($query)
    {
        return $query->where('status', self::STATUS_SENT);
    }

    public function scopeAcknowledged($query)
    {
        return $query->where('status', self::STATUS_ACKNOWLEDGED);
    }

    public function scopeResolved($query)
    {
        return $query->where('status', self::STATUS_RESOLVED);
    }

    public function getIsOverdueAttribute(): bool
    {
        return $this->status === self::STATUS_PENDING && 
               $this->due_date !== null && 
               $this->due_date < now()->format('Y-m-d');
    }

    public function getIsCriticalAttribute(): bool
    {
        return $this->priority === self::PRIORITY_CRITICAL;
    }

    public function getDaysOverdueAttribute(): ?int
    {
        if (!$this->due_date || $this->status !== self::STATUS_PENDING) {
            return null;
        }
        
        $dueDate = strtotime($this->due_date);
        $today = strtotime(now()->format('Y-m-d'));
        
        if ($today > $dueDate) {
            return (int) round(($today - $dueDate) / 86400);
        }
        
        return null;
    }

    public function markAsSent(): void
    {
        $this->status = self::STATUS_SENT;
        $this->sent_date = now()->format('Y-m-d H:i:s');
        $this->save();
    }

    public function markAsAcknowledged(): void
    {
        $this->status = self::STATUS_ACKNOWLEDGED;
        $this->acknowledged_date = now()->format('Y-m-d H:i:s');
        $this->save();
    }

    public function markAsResolved(): void
    {
        $this->status = self::STATUS_RESOLVED;
        $this->resolved_date = now()->format('Y-m-d H:i:s');
        $this->save();
    }
}
