<?php

declare(strict_types=1);

namespace App\Models\Behavior;

use App\Models\Model;
use App\Models\SchoolManagement\Student;
use App\Models\User;
use App\Traits\UsesUuid;

class InterventionPlan extends Model
{
    use UsesUuid;

    protected string $table = 'intervention_plans';

    protected array $fillable = [
        'student_id',
        'created_by',
        'assigned_to',
        'title',
        'description',
        'goals',
        'strategies',
        'status',
        'start_date',
        'end_date',
        'review_date',
        'notes',
        'is_successful',
        'updated_by',
    ];

    protected array $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'review_date' => 'date',
        'is_successful' => 'boolean',
    ];

    public function scopeActive($query)
    {
        $now = date('Y-m-d');
        return $query->where('status', 'active')
            ->where('start_date', '<=', $now)
            ->where(function ($q) use ($now) {
                $q->where('end_date', '>=', $now)->orWhereNull('end_date');
            });
    }

    public function scopeByStatus($query, string $status)
    {
        return $query->where('status', $status);
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    public function scopeForStudent($query, string $studentId)
    {
        return $query->where('student_id', $studentId);
    }

    public function student()
    {
        return $this->belongsTo(Student::class, 'student_id');
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function assignedTo()
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function updatedBy()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
}
