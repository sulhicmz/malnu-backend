<?php

declare(strict_types=1);

namespace App\Models;

use Hypervel\Database\Model\Model;
use Hypervel\Database\Model\Relations\BelongsTo;
use Hypervel\Database\Model\Relations\HasMany;

class HealthRecord extends Model
{
    protected string $primaryKey = 'id';
    protected string $keyType = 'string';
    public bool $incrementing = false;

    protected $fillable = [
        'student_id',
        'blood_type',
        'chronic_conditions',
        'dietary_restrictions',
        'family_medical_history',
        'physical_disabilities',
        'notes',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class, 'student_id', 'id');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by', 'id');
    }

    public function updatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by', 'id');
    }

    public function immunizations(): HasMany
    {
        return $this->hasMany(Immunization::class, 'health_record_id', 'id');
    }

    public function medications(): HasMany
    {
        return $this->hasMany(Medication::class, 'health_record_id', 'id');
    }

    public function allergies(): HasMany
    {
        return $this->hasMany(Allergy::class, 'health_record_id', 'id');
    }

    public function healthScreenings(): HasMany
    {
        return $this->hasMany(HealthScreening::class, 'health_record_id', 'id');
    }

    public function emergencyContacts(): HasMany
    {
        return $this->hasMany(EmergencyContact::class, 'health_record_id', 'id');
    }

    public function medicalIncidents(): HasMany
    {
        return $this->hasMany(MedicalIncident::class, 'health_record_id', 'id');
    }

    public function nurseVisits(): HasMany
    {
        return $this->hasMany(NurseVisit::class, 'health_record_id', 'id');
    }

    public function healthAlerts(): HasMany
    {
        return $this->hasMany(HealthAlert::class, 'health_record_id', 'id');
    }
}
