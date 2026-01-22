<?php

declare(strict_types=1);

namespace App\Models;

class Allergy extends Model
{
    protected string $primaryKey = 'id';
    protected string $keyType = 'string';
    public bool $incrementing = false;

    const SEVERITY_MILD = 'mild';
    const SEVERITY_MODERATE = 'moderate';
    const SEVERITY_SEVERE = 'severe';
    const SEVERITY_LIFE_THREATENING = 'life_threatening';

    protected $fillable = [
        'student_id',
        'health_record_id',
        'allergen',
        'severity',
        'symptoms',
        'emergency_protocol',
        'epipen_required',
        'diagnosed_date',
        'notes',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'epipen_required' => 'boolean',
        'diagnosed_date' => 'date',
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

    public function scopeSevere($query)
    {
        return $query->whereIn('severity', [self::SEVERITY_SEVERE, self::SEVERITY_LIFE_THREATENING]);
    }

    public function scopeRequiresEpipen($query)
    {
        return $query->where('epipen_required', true);
    }

    public function getIsSevereAttribute(): bool
    {
        return in_array($this->severity, [self::SEVERITY_SEVERE, self::SEVERITY_LIFE_THREATENING]);
    }

    public function getRequiresImmediateAttentionAttribute(): bool
    {
        return $this->severity === self::SEVERITY_LIFE_THREATENING;
    }
}
