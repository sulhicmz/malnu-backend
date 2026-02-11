<?php

declare(strict_types=1);

namespace App\Http\Controllers\Attendance;

use App\Http\Controllers\Api\BaseController;
use App\Models\Attendance\StaffAttendance;
use App\Models\SchoolManagement\Staff;
use Hyperf\HttpServer\Contract\RequestInterface;
use Hyperf\HttpServer\Contract\ResponseInterface;
use Psr\Container\ContainerInterface;

class StaffAttendanceController extends BaseController
{
    public function __construct(
        RequestInterface $request,
        ResponseInterface $response,
        ContainerInterface $container
    ) {
        parent::__construct($request, $response, $container);
    }

    /**
     * Display a listing of the staff attendance records.
     */
    public function index()
    {
        $query = StaffAttendance::with('staff');

        if ($this->request->has('staff_id')) {
            $query->where('staff_id', $this->request->input('staff_id'));
        }

        if ($this->request->has('start_date') && $this->request->has('end_date')) {
            $query->whereBetween('attendance_date', [$this->request->input('start_date'), $this->request->input('end_date')]);
        } elseif ($this->request->has('date')) {
            $query->whereDate('attendance_date', $this->request->input('date'));
        }

        if ($this->request->has('status')) {
            $query->where('status', $this->request->input('status'));
        }

        $attendances = $query->orderBy('attendance_date', 'desc')->paginate(15);
        return $this->successResponse($attendances);
    }

    /**
     * Store a newly created staff attendance record.
     */
    public function store()
    {
        $data = $this->request->all();

        if (empty($data['staff_id']) || empty($data['attendance_date'])) {
            return $this->errorResponse('Staff ID and attendance date are required', null, null, 400);
        }

        $existingAttendance = StaffAttendance::where('staff_id', $data['staff_id'])
            ->whereDate('attendance_date', $data['attendance_date'])
            ->first();

        if ($existingAttendance) {
            return $this->errorResponse('Attendance record already exists for this staff on the given date', 'DUPLICATE_ATTENDANCE', null, 400);
        }

        $attendance = StaffAttendance::create($data);
        return $this->successResponse($attendance, 'Staff attendance recorded successfully', 201);
    }

    /**
     * Display the specified staff attendance record.
     */
    public function show(string $id)
    {
        $attendance = StaffAttendance::with('staff')->find($id);

        if (! $attendance) {
            return $this->notFoundResponse('Attendance record not found');
        }

        return $this->successResponse($attendance);
    }

    /**
     * Update the specified staff attendance record.
     */
    public function update(string $id)
    {
        $attendance = StaffAttendance::find($id);

        if (! $attendance) {
            return $this->notFoundResponse('Attendance record not found');
        }

        $data = $this->request->all();
        $attendance->update($data);

        return $this->successResponse($attendance, 'Attendance record updated successfully');
    }

    /**
     * Remove the specified staff attendance record.
     */
    public function destroy(string $id)
    {
        $attendance = StaffAttendance::find($id);

        if (! $attendance) {
            return $this->notFoundResponse('Attendance record not found');
        }

        $attendance->delete();
        return $this->successResponse(null, 'Attendance record deleted successfully');
    }

    /**
     * Mark attendance for a staff member (check-in/check-out).
     */
    public function markAttendance()
    {
        $data = $this->request->all();

        if (empty($data['staff_id']) || empty($data['attendance_date']) || empty($data['action'])) {
            return $this->errorResponse('Staff ID, attendance date, and action are required', null, null, 400);
        }

        if (! in_array($data['action'], ['check_in', 'check_out'])) {
            return $this->errorResponse('Action must be either check_in or check_out', null, null, 400);
        }

        $attendance = StaffAttendance::firstOrCreate(
            [
                'staff_id' => $data['staff_id'],
                'attendance_date' => $data['attendance_date'],
            ],
            [
                'status' => 'absent',
            ]
        );

        if ($data['action'] === 'check_in') {
            $attendance->update([
                'check_in_time' => $data['time'],
                'check_in_method' => 'manual',
                'status' => 'present',
            ]);
        } elseif ($data['action'] === 'check_out') {
            $attendance->update([
                'check_out_time' => $data['time'],
                'check_out_method' => 'manual',
            ]);
        }

        return $this->successResponse($attendance, 'Attendance marked successfully');
    }
}
