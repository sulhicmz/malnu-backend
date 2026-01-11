<?php

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

    public function index()
    {
        try {
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

            return $this->successResponse($attendances, 'Staff attendances retrieved successfully');
        } catch (\Exception $e) {
            return $this->serverErrorResponse('Failed to retrieve staff attendances');
        }
    }

    public function store()
    {
        try {
            $data = $this->request->all();

            $requiredFields = ['staff_id', 'attendance_date', 'status'];
            $errors = [];

            foreach ($requiredFields as $field) {
                if (empty($data[$field])) {
                    $errors[$field] = ["The {$field} field is required."];
                }
            }

            if (isset($data['check_out_time']) && isset($data['check_in_time']) && $data['check_out_time'] <= $data['check_in_time']) {
                $errors['check_out_time'] = ['The check out time must be after check in time.'];
            }

            $validStatuses = ['present', 'absent', 'late', 'early_departure', 'on_leave'];
            if (isset($data['status']) && !in_array($data['status'], $validStatuses)) {
                $errors['status'] = ['The status must be one of: present, absent, late, early_departure, on_leave.'];
            }

            if (!empty($errors)) {
                return $this->validationErrorResponse($errors);
            }

            $existingAttendance = StaffAttendance::where('staff_id', $data['staff_id'])
                ->whereDate('attendance_date', $data['attendance_date'])
                ->first();

            if ($existingAttendance) {
                return $this->errorResponse('Attendance record already exists for this staff on given date', 'DUPLICATE_ATTENDANCE');
            }

            $attendance = StaffAttendance::create($data);

            return $this->successResponse($attendance, 'Staff attendance recorded successfully', 201);
        } catch (\Exception $e) {
            return $this->serverErrorResponse('Failed to create staff attendance');
        }
    }

    public function show(string $id)
    {
        try {
            $attendance = StaffAttendance::with('staff')->find($id);

            if (!$attendance) {
                return $this->notFoundResponse('Staff attendance record not found');
            }

            return $this->successResponse($attendance, 'Staff attendance retrieved successfully');
        } catch (\Exception $e) {
            return $this->serverErrorResponse('Failed to retrieve staff attendance');
        }
    }

    public function update(string $id)
    {
        try {
            $attendance = StaffAttendance::find($id);

            if (!$attendance) {
                return $this->notFoundResponse('Staff attendance record not found');
            }

            $data = $this->request->all();

            $errors = [];

            if (isset($data['check_out_time']) && isset($data['check_in_time']) && $data['check_out_time'] <= $data['check_in_time']) {
                $errors['check_out_time'] = ['The check out time must be after check in time.'];
            }

            $validStatuses = ['present', 'absent', 'late', 'early_departure', 'on_leave'];
            if (isset($data['status']) && !in_array($data['status'], $validStatuses)) {
                $errors['status'] = ['The status must be one of: present, absent, late, early_departure, on_leave.'];
            }

            if (!empty($errors)) {
                return $this->validationErrorResponse($errors);
            }

            $attendance->update($data);

            return $this->successResponse($attendance, 'Staff attendance updated successfully');
        } catch (\Exception $e) {
            return $this->serverErrorResponse('Failed to update staff attendance');
        }
    }

    public function destroy(string $id)
    {
        try {
            $attendance = StaffAttendance::find($id);

            if (!$attendance) {
                return $this->notFoundResponse('Staff attendance record not found');
            }

            $attendance->delete();

            return $this->successResponse(null, 'Staff attendance deleted successfully');
        } catch (\Exception $e) {
            return $this->serverErrorResponse('Failed to delete staff attendance');
        }
    }

    public function markAttendance()
    {
        try {
            $data = $this->request->all();

            $requiredFields = ['staff_id', 'attendance_date', 'action', 'time'];
            $errors = [];

            foreach ($requiredFields as $field) {
                if (empty($data[$field])) {
                    $errors[$field] = ["The {$field} field is required."];
                }
            }

            $validActions = ['check_in', 'check_out'];
            if (isset($data['action']) && !in_array($data['action'], $validActions)) {
                $errors['action'] = ['The action must be either check_in or check_out.'];
            }

            if (!empty($errors)) {
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
                    'status' => 'present'
                ]);
            } elseif ($data['action'] === 'check_out') {
                $attendance->update([
                    'check_out_time' => $data['time'],
                    'check_out_method' => 'manual'
                ]);
            }

            return $this->successResponse($attendance, 'Attendance marked successfully');
        } catch (\Exception $e) {
            return $this->serverErrorResponse('Failed to mark attendance');
        }
    }
}
