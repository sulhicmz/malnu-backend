<?php

declare(strict_types=1);

namespace App\Models;

class Medication extends Model
{
    public const STATUS_ACTIVE = 'active';

    public const STATUS_COMPLETED = 'completed';

    public const STATUS_DISCONTINUED = 'discontinued';

    public const STATUS_ON_HOLD = 'on_hold';

    protected $fillable = [
        'student_id',
        'health_record_id',
        'medication_name',
        'dosage',
        'frequency',
        'administration_route',
        'start_date',
        'end_date',
        'refrigeration_required',
        'parent_consent',
        'status',
        'notes',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'refrigeration_required' => 'boolean',
        'parent_consent' => 'boolean',
        'start_date' => 'date',
        'end_date' => 'date',
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

    public function scopeActive($query)
    {
        return $query->where('status', self::STATUS_ACTIVE);
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', self::STATUS_COMPLETED);
    }

    public function scopeRequiresRefrigeration($query)
    {
        return $query->where('refrigeration_required', true);
    }

    public function getIsActiveAttribute(): bool
    {
        return $this->status === self::STATUS_ACTIVE
               && (! $this->end_date || $this->end_date >= now()->format('Y-m-d'));
    }
}
