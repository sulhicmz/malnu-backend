<?php

declare(strict_types=1);

namespace App\Models;

use Hypervel\Database\Model\Model as BaseModel;
use Hypervel\Database\Model\Relations\BelongsTo;
use Hypervel\Database\Model\Relations\HasMany;

class HealthEmergency extends BaseModel
{
    protected $table = 'health_emergencies';

    protected $fillable = [
        'student_id',
        'health_record_id',
        'emergency_type',
        'emergency_contact_name',
        'emergency_contact_phone',
        'emergency_contact_relationship',
        'medical_condition',
        'instructions',
        'is_critical',
        'requires_medical_attention',
        'notified',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'is_critical' => 'boolean',
        'requires_medical_attention' => 'boolean',
        'notified' => 'boolean',
    ];

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class, 'student_id', 'id');
    }

    public function healthRecord(): BelongsTo
    {
        return $this->belongsTo(HealthRecord::class, 'health_record_id', 'id');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by', 'id');
    }

    public function updatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by', 'id');
    }

    public function scopeCritical($query)
    {
        return $query->where('is_critical', true);
    }
}
