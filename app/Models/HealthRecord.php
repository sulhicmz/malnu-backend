<?php

declare(strict_types=1);

namespace App\Models;

use Hyperf\Database\Eloquent\Model;

class HealthRecord extends Model
{
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

    public function student()
    {
        return $this->belongsTo(Student::class, 'student_id', 'id');
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by', 'id');
    }

    public function updatedBy()
    {
        return $this->belongsTo(User::class, 'updated_by', 'id');
    }

    public function medications()
    {
        return $this->hasMany(Medication::class, 'health_record_id', 'id');
    }

    public function immunizations()
    {
        return $this->hasMany(Immunization::class, 'health_record_id', 'id');
    }

    public function allergies()
    {
        return $this->hasMany(Allergy::class, 'health_record_id', 'id');
    }

    public function healthScreenings()
    {
        return $this->hasMany(HealthScreening::class, 'health_record_id', 'id');
    }

    public function emergencyContacts()
    {
        return $this->hasMany(EmergencyContact::class, 'health_record_id', 'id');
    }

    public function medicalIncidents()
    {
        return $this->hasMany(MedicalIncident::class, 'health_record_id', 'id');
    }

    public function nurseVisits()
    {
        return $this->hasMany(NurseVisit::class, 'health_record_id', 'id');
    }

    public function healthAlerts()
    {
        return $this->hasMany(HealthAlert::class, 'health_record_id', 'id');
    }

    public function scopeActive($query)
    {
        return $query->whereNull('deleted_at');
    }

    public function scopeForStudent($query, string $studentId)
    {
        return $query->where('student_id', $studentId);
    }

    public function getHasSevereAllergiesAttribute(): bool
    {
        return $this->allergies()->whereIn('severity', ['severe', 'life_threatening'])->exists();
    }
}
