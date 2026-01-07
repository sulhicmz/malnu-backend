<?php

declare(strict_types=1);

namespace App\Models\HealthManagement;

use App\Models\Model;
use App\Models\SchoolManagement\Student;
use App\Models\User;

class HealthScreening extends Model
{
    protected $primaryKey = 'id';
    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'student_id',
        'screening_type',
        'screening_date',
        'results',
        'findings',
        'status',
        'recommendations',
        'follow_up_date',
        'performed_by',
        'notes',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'screening_date' => 'date',
        'follow_up_date' => 'date',
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

    public function scopeAbnormal($query)
    {
        return $query->where('status', 'abnormal');
    }

    public function scopeNeedsFollowUp($query)
    {
        return $query->where('status', 'needs_follow_up');
    }

    public function scopeVision($query)
    {
        return $query->where('screening_type', 'vision');
    }

    public function scopeHearing($query)
    {
        return $query->where('screening_type', 'hearing');
    }

    public function needsFollowUp(): bool
    {
        return in_array($this->status, ['abnormal', 'needs_follow_up']);
    }
}
