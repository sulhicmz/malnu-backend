<?php

declare(strict_types=1);

namespace App\Models\HealthManagement;

use App\Models\Model;
use App\Models\SchoolManagement\Student;
use App\Models\User;

class HealthRecord extends Model
{
    protected $primaryKey = 'id';
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'student_id',
        'blood_type',
        'medical_history',
        'chronic_conditions',
        'previous_surgeries',
        'family_medical_history',
        'dietary_restrictions',
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

    public function medications()
    {
        return $this->hasMany(Medication::class, 'student_id', 'student_id');
    }

    public function immunizations()
    {
        return $this->hasMany(Immunization::class, 'student_id', 'student_id');
    }

    public function allergies()
    {
        return $this->hasMany(Allergy::class, 'student_id', 'student_id');
    }

    public function healthScreenings()
    {
        return $this->hasMany(HealthScreening::class, 'student_id', 'student_id');
    }

    public function emergencyContacts()
    {
        return $this->hasMany(EmergencyContact::class, 'student_id', 'student_id');
    }

    public function medicalIncidents()
    {
        return $this->hasMany(MedicalIncident::class, 'student_id', 'student_id');
    }

    public function nurseVisits()
    {
        return $this->hasMany(NurseVisit::class, 'student_id', 'student_id');
    }

    public function healthAlerts()
    {
        return $this->hasMany(HealthAlert::class, 'student_id', 'student_id');
    }
}
