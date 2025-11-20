<?php

namespace App\Models\Attendance;

use App\Models\Model;
use App\Models\SchoolManagement\ClassSubject;
use App\Traits\UsesUuid;

/**
 * @property string $id
 * @property string $leave_request_id
 * @property string $substitute_teacher_id
 * @property string|null $class_subject_id
 * @property string $assignment_date
 * @property string $status
 * @property string|null $assignment_notes
 * @property float|null $payment_amount
 */
class SubstituteAssignment extends Model
{
    use UsesUuid;

    protected $table = 'substitute_assignments';

    protected $fillable = [
        'leave_request_id',
        'substitute_teacher_id',
        'class_subject_id',
        'assignment_date',
        'status',
        'assignment_notes',
        'payment_amount',
    ];

    protected $casts = [
        'assignment_date' => 'date',
        'payment_amount' => 'decimal:2',
    ];

    /**
     * Get the leave request associated with this assignment.
     */
    public function leaveRequest()
    {
        return $this->belongsTo(LeaveRequest::class, 'leave_request_id');
    }

    /**
     * Get the substitute teacher for this assignment.
     */
    public function substituteTeacher()
    {
        return $this->belongsTo(SubstituteTeacher::class, 'substitute_teacher_id');
    }

    /**
     * Get the class subject for this assignment.
     */
    public function classSubject()
    {
        return $this->belongsTo(ClassSubject::class, 'class_subject_id');
    }
}