<?php

declare(strict_types=1);

namespace App\Models\Analytics;

use App\Models\Model;
use App\Models\SchoolManagement\Student;
use App\Models\User;

class EarlyWarning extends Model
{
    public $incrementing = false;

    protected $primaryKey = 'id';

    protected $keyType = 'string';

    protected $table = 'early_warnings';

    protected $fillable = [
        'student_id',
        'warning_type',
        'severity',
        'description',
        'trigger_data',
        'status',
        'triggered_at',
        'acknowledged_at',
        'acknowledged_by',
        'resolved_at',
        'resolution_notes',
    ];

    protected $casts = [
        'trigger_data' => 'array',
        'triggered_at' => 'datetime',
        'acknowledged_at' => 'datetime',
        'resolved_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function acknowledgedBy()
    {
        return $this->belongsTo(User::class, 'acknowledged_by');
    }

    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    public function isHighSeverity(): bool
    {
        return in_array($this->severity, ['high', 'critical'], true);
    }

    public function acknowledge(string $userId): void
    {
        $this->update([
            'status' => 'acknowledged',
            'acknowledged_at' => now(),
            'acknowledged_by' => $userId,
        ]);
    }

    public function resolve(string $notes = null): void
    {
        $this->update([
            'status' => 'resolved',
            'resolved_at' => now(),
            'resolution_notes' => $notes,
        ]);
    }
}
