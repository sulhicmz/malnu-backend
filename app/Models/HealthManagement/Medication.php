<?php

declare(strict_types=1);

namespace App\Models\HealthManagement;

use App\Models\Model;
use App\Models\SchoolManagement\Student;
use App\Models\User;

class Medication extends Model
{
    protected $primaryKey = 'id';
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'student_id',
        'medication_name',
        'dosage',
        'frequency',
        'administration_method',
        'instructions',
        'prescribing_physician',
        'prescription_number',
        'start_date',
        'end_date',
        'administration_time',
        'requires_refrigeration',
        'status',
        'discontinuation_reason',
        'school_nurse_id',
        'parent_consent',
        'parent_consent_date',
        'notes',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'administration_time' => 'datetime',
        'requires_refrigeration' => 'boolean',
        'parent_consent' => 'boolean',
        'parent_consent_date' => 'date',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function schoolNurse()
    {
        return $this->belongsTo(User::class, 'school_nurse_id');
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updatedBy()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }
}
