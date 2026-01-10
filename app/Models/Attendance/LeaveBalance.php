<?php

namespace App\Models\Attendance;

use App\Models\Model;
use App\Models\SchoolManagement\Staff;
use App\Traits\UsesUuid;
use App\Models\Attendance\LeaveType;

/**
 * @property string $id
 * @property string $staff_id
 * @property string $leave_type_id
 * @property int $current_balance
 * @property int $used_days
 * @property int $allocated_days
 * @property int $carry_forward_days
 * @property int $year
 */
class LeaveBalance extends Model
{
    use UsesUuid;

    protected string $primaryKey = 'id';
    protected string $keyType = 'string';
    public bool $incrementing = false;

    protected $table = 'leave_balances';

    protected $fillable = [
        'staff_id',
        'leave_type_id',
        'current_balance',
        'used_days',
        'allocated_days',
        'carry_forward_days',
        'year',
    ];

    protected $casts = [
        'current_balance' => 'integer',
        'used_days' => 'integer',
        'allocated_days' => 'integer',
        'carry_forward_days' => 'integer',
        'year' => 'integer',
    ];

    /**
     * Get the staff record associated with this leave balance.
     */
    public function staff()
    {
        return $this->belongsTo(Staff::class, 'staff_id');
    }

    /**
     * Get the leave type for this balance.
     */
    public function leaveType()
    {
        return $this->belongsTo(LeaveType::class, 'leave_type_id');
    }
}