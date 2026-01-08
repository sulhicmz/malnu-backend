<?php

declare(strict_types=1);

namespace App\Models\HealthManagement;

use App\Models\Model;
use App\Models\SchoolManagement\Student;
use App\Models\User;

class Allergy extends Model
{
    protected $primaryKey = 'id';
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'student_id',
        'allergen',
        'allergy_type',
        'severity',
        'reactions',
        'diagnosis_date',
        'emergency_protocol',
        'requires_epipen',
        'treatment_plan',
        'notes',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'diagnosis_date' => 'date',
        'requires_epipen' => 'boolean',
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

    public function scopeSevere($query)
    {
        return $query->whereIn('severity', ['severe', 'life_threatening']);
    }

    public function scopeLifeThreatening($query)
    {
        return $query->where('severity', 'life_threatening');
    }

    public function isLifeThreatening(): bool
    {
        return $this->severity === 'life_threatening';
    }

    public function requiresEmergencyAction(): bool
    {
        return in_array($this->severity, ['severe', 'life_threatening']);
    }
}
