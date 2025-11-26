<?php

namespace App\Models\Attendance;

use App\Models\Model;
use App\Traits\UsesUuid;
use App\Models\Attendance\LeaveRequest;
use App\Models\Attendance\LeaveBalance;

/**
 * @property string $id
 * @property string $name
 * @property string $code
 * @property string|null $description
 * @property int|null $max_days_per_year
 * @property bool $is_paid
 * @property bool $requires_approval
 * @property array|null $eligibility_criteria
 * @property bool $is_active
 */
class LeaveType extends Model
{
    use UsesUuid;

    protected $table = 'leave_types';

    protected $fillable = [
        'name',
        'code',
        'description',
        'max_days_per_year',
        'is_paid',
        'requires_approval',
        'eligibility_criteria',
        'is_active',
    ];

    protected $casts = [
        'max_days_per_year' => 'integer',
        'is_paid' => 'boolean',
        'requires_approval' => 'boolean',
        'eligibility_criteria' => 'array',
        'is_active' => 'boolean',
    ];

    /**
     * Get the leave requests for this leave type.
     */
    public function leaveRequests()
    {
        return $this->hasMany(LeaveRequest::class, 'leave_type_id');
    }

    /**
     * Get the leave balances for this leave type.
     */
    public function leaveBalances()
    {
        return $this->hasMany(LeaveBalance::class, 'leave_type_id');
    }
}