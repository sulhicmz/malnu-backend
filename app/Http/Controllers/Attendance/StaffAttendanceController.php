<?php

declare(strict_types=1);

namespace App\Http\Controllers\Attendance;

use App\Enums\ErrorCode;
use App\Http\Controllers\Api\BaseController;
use App\Models\Attendance\StaffAttendance;
use App\Models\SchoolManagement\Staff;
use Exception;
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
     * Display a listing of staff attendance records.
     */
    public function index()
    {
        try {
            $query = StaffAttendance::with('staff');

            // Filter by staff ID if provided
            if ($this->request->has('staff_id')) {
                $query->where('staff_id', $this->request->input('staff_id'));
            }

            // Filter by date range if provided
            if ($this->request->has('start_date') && $this->request->has('end_date')) {
                $query->whereBetween('attendance_date', [$this->request->input('start_date'), $this->request->input('end_date')]);
            } elseif ($this->request->has('date')) {
                $query->whereDate('attendance_date', $this->request->input('date'));
            }

            // Filter by status if provided
            if ($this->request->has('status')) {
                $query->where('status', $this->request->input('status'));
            }

            $attendances = $query->orderBy('attendance_date', 'desc')->paginate(15);

            return $this->successResponse($attendances);
        } catch (Exception $e) {
            return $this->serverErrorResponse('Failed to retrieve attendance records');
        }
    }

    /**
     * Store a newly created staff attendance record.
     */
    public function store()
    {
        try {
            $data = $this->request->all();

            $errors = [];

            if (empty($data['staff_id'])) {
                $errors['staff_id'] = ['The staff_id field is required.'];
            }

            if (empty($data['attendance_date'])) {
                $errors['attendance_date'] = ['The attendance_date field is required.'];
            }

            if (empty($data['status'])) {
                $errors['status'] = ['The status field is required.'];
            }

            $validStatuses = ['present', 'absent', 'late', 'early_departure', 'on_leave'];
            if (isset($data['status']) && ! in_array($data['status'], $validStatuses)) {
                $errors['status'] = ['The status field must be one of: present, absent, late, early_departure, on_leave.'];
            }

            if (! empty($errors)) {
                return $this->validationErrorResponse($errors);
            }

            // Check if attendance record already exists for same staff and date
            $existingAttendance = StaffAttendance::where('staff_id', $data['staff_id'])
                ->whereDate('attendance_date', $data['attendance_date'])
                ->first();

            if ($existingAttendance) {
                return $this->errorResponse('Attendance record already exists for this staff on given date', ErrorCode::ATTENDANCE_ALREADY_MARKED, null, ErrorCode::getStatusCode(ErrorCode::ATTENDANCE_ALREADY_MARKED));
            }

            $attendance = StaffAttendance::create($data);

            return $this->successResponse($attendance, 'Staff attendance recorded successfully', 201);
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage(), ErrorCode::ATTENDANCE_ERROR, null, ErrorCode::getStatusCode(ErrorCode::ATTENDANCE_ERROR));
        }
    }

    /**
     * Display specified staff attendance record.
     */
    public function show(string $id)
    {
        try {
            $attendance = StaffAttendance::with('staff')->find($id);

            if (! $attendance) {
                return $this->notFoundResponse('Attendance record not found');
            }

            return $this->successResponse($attendance);
        } catch (Exception $e) {
            return $this->serverErrorResponse('Failed to retrieve attendance record');
        }
    }

    /**
     * Update specified staff attendance record.
     */
    public function update(string $id)
    {
        try {
            $attendance = StaffAttendance::find($id);

            if (! $attendance) {
                return $this->notFoundResponse('Attendance record not found');
            }

            $data = $this->request->all();

            $errors = [];

            $validStatuses = ['present', 'absent', 'late', 'early_departure', 'on_leave'];
            if (isset($data['status']) && ! in_array($data['status'], $validStatuses)) {
                $errors['status'] = ['The status field must be one of: present, absent, late, early_departure, on_leave.'];
            }

            if (! empty($errors)) {
                return $this->validationErrorResponse($errors);
            }

            $attendance->update($data);

            return $this->successResponse($attendance, 'Attendance record updated successfully');
        } catch (Exception $e) {
            return $this->serverErrorResponse('Failed to update attendance record');
        }
    }

    /**
     * Remove specified staff attendance record.
     */
    public function destroy(string $id)
    {
        try {
            $attendance = StaffAttendance::find($id);

            if (! $attendance) {
                return $this->notFoundResponse('Attendance record not found');
            }

            $attendance->delete();

            return $this->successResponse(null, 'Attendance record deleted successfully');
        } catch (Exception $e) {
            return $this->serverErrorResponse('Failed to delete attendance record');
        }
    }

    /**
     * Mark attendance for a staff member (check-in/check-out).
     */
    public function markAttendance()
    {
        try {
            $data = $this->request->all();

            $errors = [];

            if (empty($data['staff_id'])) {
                $errors['staff_id'] = ['The staff_id field is required.'];
            }

            if (empty($data['attendance_date'])) {
                $errors['attendance_date'] = ['The attendance_date field is required.'];
            }

            if (empty($data['action'])) {
                $errors['action'] = ['The action field is required.'];
            }

            if (empty($data['time'])) {
                $errors['time'] = ['The time field is required.'];
            }

            $validActions = ['check_in', 'check_out'];
            if (isset($data['action']) && ! in_array($data['action'], $validActions)) {
                $errors['action'] = ['The action field must be either check_in or check_out.'];
            }

            if (! empty($errors)) {
                return $this->validationErrorResponse($errors);
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
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage(), ErrorCode::ATTENDANCE_ERROR, null, ErrorCode::getStatusCode(ErrorCode::ATTENDANCE_ERROR));
        }
    }
}
