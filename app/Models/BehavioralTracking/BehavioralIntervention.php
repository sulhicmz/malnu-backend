<?php

declare(strict_types=1);

namespace App\Models\BehavioralTracking;

use App\Models\Model;
use App\Models\SchoolManagement\Student;
use App\Models\User;

class BehavioralIntervention extends Model
{
    protected $primaryKey = 'id';
    protected $keyType    = 'string';
    public $incrementing  = false;

    protected $fillable = [
        'incident_id',
        'student_id',
        'intervention_by',
        'intervention_type',
        'description',
        'status',
        'planned_date',
        'completed_date',
        'outcome',
        'parent_notified',
        'is_effective',
    ];

    protected $casts = [
        'parent_notified'  => 'boolean',
        'is_effective'     => 'boolean',
        'planned_date'     => 'datetime',
        'completed_date'    => 'datetime',
        'created_at'       => 'datetime',
        'updated_at'       => 'datetime',
    ];

    public function incident()
    {
        return $this->belongsTo(BehavioralIncident::class, 'incident_id');
    }

    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function interventionBy()
    {
        return $this->belongsTo(User::class, 'intervention_by');
    }
}
