<?php

declare(strict_types=1);

namespace App\Models\Attendance;

use App\Models\Model;
use App\Models\SchoolManagement\Staff;
use App\Traits\UsesUuid;

/**
 * @property string $id
 * @property string $staff_id
 * @property string $attendance_date
 * @property null|string $check_in_time
 * @property null|string $check_out_time
 * @property string $status
 * @property null|string $notes
 * @property string $check_in_method
 * @property string $check_out_method
 */
class StaffAttendance extends Model
{
    use UsesUuid;

    protected $table = 'staff_attendances';

    protected $fillable = [
        'staff_id',
        'attendance_date',
        'check_in_time',
        'check_out_time',
        'status',
        'notes',
        'check_in_method',
        'check_out_method',
    ];

    protected $casts = [
        'attendance_date' => 'date',
        'check_in_time' => 'time',
        'check_out_time' => 'time',
    ];

    /**
     * Get the staff record associated with this attendance.
     */
    public function staff()
    {
        return $this->belongsTo(Staff::class, 'staff_id');
    }
}
