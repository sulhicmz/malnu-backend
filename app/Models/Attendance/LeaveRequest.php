<?php

declare(strict_types=1);

namespace App\Models\Attendance;

use App\Models\Model;
use App\Models\SchoolManagement\Staff;
use App\Models\SchoolManagement\Teacher;
use App\Models\User;
use App\Traits\UsesUuid;

/**
 * @property string $id
 * @property string $staff_id
 * @property string $leave_type_id
 * @property string $start_date
 * @property string $end_date
 * @property int $total_days
 * @property string $reason
 * @property null|string $comments
 * @property string $status
 * @property null|string $approved_by
 * @property null|string $approved_at
 * @property null|string $approval_comments
 * @property null|string $substitute_assigned_id
 */
class LeaveRequest extends Model
{
    protected string $primaryKey = 'id';
    protected string $keyType = 'string';
    public bool $incrementing = false;

    use UsesUuid;

    protected $table = 'leave_requests';

    protected $fillable = [
        'staff_id',
        'leave_type_id',
        'start_date',
        'end_date',
        'total_days',
        'reason',
        'comments',
        'status',
        'approved_by',
        'approved_at',
        'approval_comments',
        'substitute_assigned_id',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'total_days' => 'integer',
        'approved_at' => 'datetime',
    ];

    /**
     * Get the staff record associated with this leave request.
     */
    public function staff()
    {
        return $this->belongsTo(Staff::class, 'staff_id');
    }

    /**
     * Get the leave type for this request.
     */
    public function leaveType()
    {
        return $this->belongsTo(LeaveType::class, 'leave_type_id');
    }

    /**
     * Get the user who approved this request.
     */
    public function approvedBy()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    /**
     * Get the substitute teacher assigned to this request.
     */
    public function substituteAssigned()
    {
        return $this->belongsTo(Teacher::class, 'substitute_assigned_id');
    }

    /**
     * Get the substitute assignments for this leave request.
     */
    public function substituteAssignments()
    {
        return $this->hasMany(SubstituteAssignment::class, 'leave_request_id');
    }
}
