<?php

declare(strict_types=1);

namespace App\Models\HealthManagement;

use App\Models\Model;
use App\Models\SchoolManagement\Student;
use App\Models\User;

class EmergencyContact extends Model
{
    protected $primaryKey = 'id';
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'student_id',
        'full_name',
        'relationship',
        'phone',
        'secondary_phone',
        'email',
        'address',
        'primary_contact',
        'authorized_pickup',
        'medical_consent',
        'medical_consent_date',
        'notes',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'primary_contact' => 'boolean',
        'authorized_pickup' => 'boolean',
        'medical_consent' => 'boolean',
        'medical_consent_date' => 'date',
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

    public function scopePrimary($query)
    {
        return $query->where('primary_contact', true);
    }

    public function scopeAuthorizedPickup($query)
    {
        return $query->where('authorized_pickup', true);
    }

    public function scopeWithMedicalConsent($query)
    {
        return $query->where('medical_consent', true);
    }

    public function isPrimary(): bool
    {
        return $this->primary_contact;
    }

    public function canPickup(): bool
    {
        return $this->authorized_pickup;
    }

    public function hasMedicalConsent(): bool
    {
        return $this->medical_consent;
    }
}
