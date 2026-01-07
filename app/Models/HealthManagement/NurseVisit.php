<?php

declare(strict_types=1);

namespace App\Models\HealthManagement;

use App\Models\Model;
use App\Models\SchoolManagement\Student;
use App\Models\User;

class NurseVisit extends Model
{
    protected $primaryKey = 'id';
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'student_id',
        'visit_date',
        'visit_reason',
        'complaint',
        'symptoms',
        'examination',
        'treatment',
        'medication_given',
        'disposition',
        'return_time',
        'parent_notified',
        'parent_notification_time',
        'referral',
        'referral_details',
        'nurse_id',
        'notes',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'visit_date' => 'datetime',
        'return_time' => 'datetime',
        'parent_notified' => 'boolean',
        'parent_notification_time' => 'datetime',
        'referral' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function nurse()
    {
        return $this->belongsTo(User::class, 'nurse_id');
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updatedBy()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function scopeByNurse($query, $nurseId)
    {
        return $query->where('nurse_id', $nurseId);
    }

    public function scopeWithReferral($query)
    {
        return $query->where('referral', true);
    }

    public function scopeParentNotified($query)
    {
        return $query->where('parent_notified', true);
    }

    public function hasReferral(): bool
    {
        return $this->referral;
    }

    public function isParentNotified(): bool
    {
        return $this->parent_notified;
    }
}
