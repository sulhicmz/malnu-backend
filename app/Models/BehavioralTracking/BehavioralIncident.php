<?php

declare(strict_types=1);

namespace App\Models\BehavioralTracking;

use App\Models\Model;
use App\Models\SchoolManagement\Student;
use App\Models\User;

class BehavioralIncident extends Model
{
    protected $primaryKey = 'id';
    protected $keyType    = 'string';
    public $incrementing  = false;

    protected $fillable = [
        'student_id',
        'reported_by',
        'incident_type',
        'severity',
        'description',
        'action_taken',
        'incident_date',
        'is_resolved',
        'resolved_at',
        'resolved_by',
    ];

    protected $casts = [
        'incident_date' => 'datetime',
        'resolved_at'    => 'datetime',
        'is_resolved'    => 'boolean',
        'created_at'      => 'datetime',
        'updated_at'      => 'datetime',
    ];

    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function reportedBy()
    {
        return $this->belongsTo(User::class, 'reported_by');
    }

    public function resolvedBy()
    {
        return $this->belongsTo(User::class, 'resolved_by');
    }

    public function interventions()
    {
        return $this->hasMany(BehavioralIntervention::class, 'incident_id');
    }
}
