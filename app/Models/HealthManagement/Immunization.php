<?php

declare(strict_types=1);

namespace App\Models\HealthManagement;

use App\Models\Model;
use App\Models\SchoolManagement\Student;
use App\Models\User;

class Immunization extends Model
{
    protected $primaryKey = 'id';
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'student_id',
        'vaccine_name',
        'vaccine_type',
        'manufacturer',
        'lot_number',
        'administration_date',
        'next_due_date',
        'administering_facility',
        'administering_physician',
        'status',
        'exemption_reason',
        'notes',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'administration_date' => 'date',
        'next_due_date' => 'date',
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

    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    public function scopeDue($query)
    {
        return $query->where('status', 'due');
    }

    public function scopeOverdue($query)
    {
        return $query->where('status', 'overdue');
    }

    public function isOverdue(): bool
    {
        return $this->status === 'overdue' || 
               ($this->next_due_date && $this->next_due_date->isPast());
    }
}
