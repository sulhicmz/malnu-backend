<?php

declare(strict_types=1);

namespace App\Models;

class EmergencyContact extends Model
{
    protected string $primaryKey = 'id';
    protected string $keyType = 'string';
    public bool $incrementing = false;
    protected $fillable = [
        'student_id',
        'health_record_id',
        'full_name',
        'relationship',
        'phone',
        'secondary_phone',
        'email',
        'address',
        'is_primary',
        'authorized_pickup',
        'medical_consent',
        'notes',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'is_primary' => 'boolean',
        'authorized_pickup' => 'boolean',
        'medical_consent' => 'boolean',
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

    public function scopePrimary($query)
    {
        return $query->where('is_primary', true);
    }

    public function scopeAuthorizedPickup($query)
    {
        return $query->where('authorized_pickup', true);
    }

    public function scopeHasMedicalConsent($query)
    {
        return $query->where('medical_consent', true);
    }
}
