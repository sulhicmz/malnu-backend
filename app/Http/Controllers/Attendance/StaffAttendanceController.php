<?php

namespace App\Http\Controllers\Attendance;

use App\Http\Controllers\Controller;
use App\Models\Attendance\StaffAttendance;
use App\Models\SchoolManagement\Staff;
use Hyperf\HttpServer\Contract\RequestInterface;
use Hyperf\HttpServer\Contract\ResponseInterface;

class StaffAttendanceController extends Controller
{
    /**
     * Display a listing of the staff attendance records.
     */
    public function index(RequestInterface $request): ResponseInterface
    {
        $query = StaffAttendance::with('staff');

        // Filter by staff ID if provided
        $staffId = $request->input('staff_id');
        if ($staffId) {
            $query->where('staff_id', $staffId);
        }

        // Filter by date range if provided
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');
        $date = $request->input('date');
        
        if ($startDate && $endDate) {
            $query->whereBetween('attendance_date', [$startDate, $endDate]);
        } elseif ($date) {
            $query->whereDate('attendance_date', $date);
        }

        // Filter by status if provided
        $status = $request->input('status');
        if ($status) {
            $query->where('status', $status);
        }

        $attendances = $query->orderBy('attendance_date', 'desc')->paginate(15);

        return $this->response->json([
            'success' => true,
            'data' => $attendances
        ]);
    }

    /**
     * Store a newly created staff attendance record.
     */
    public function store(RequestInterface $request): ResponseInterface
    {
        $requestData = $request->all();
        
        // Basic validation
        if (empty($requestData['staff_id'])) {
            return $this->response->json([
                'success' => false,
                'message' => 'Staff ID is required'
            ], 422);
        }
        
        if (empty($requestData['attendance_date'])) {
            return $this->response->json([
                'success' => false,
                'message' => 'Attendance date is required'
            ], 422);
        }
        
        if (empty($requestData['status'])) {
            return $this->response->json([
                'success' => false,
                'message' => 'Status is required'
            ], 422);
        }

        // Check if attendance record already exists for the same staff and date
        $existingAttendance = StaffAttendance::where('staff_id', $requestData['staff_id'])
            ->whereDate('attendance_date', $requestData['attendance_date'])
            ->first();

        if ($existingAttendance) {
            return $this->response->json([
                'success' => false,
                'message' => 'Attendance record already exists for this staff on the given date'
            ], 400);
        }

        $attendance = StaffAttendance::create($requestData);

        return $this->response->json([
            'success' => true,
            'message' => 'Staff attendance recorded successfully',
            'data' => $attendance
        ], 201);
    }

    /**
     * Display the specified staff attendance record.
     */
    public function show(string $id): ResponseInterface
    {
        $attendance = StaffAttendance::with('staff')->find($id);

        if (!$attendance) {
            return $this->response->json([
                'success' => false,
                'message' => 'Attendance record not found'
            ], 404);
        }

        return $this->response->json([
            'success' => true,
            'data' => $attendance
        ]);
    }

    /**
     * Update the specified staff attendance record.
     */
    public function update(RequestInterface $request, string $id): ResponseInterface
    {
        $attendance = StaffAttendance::find($id);

        if (!$attendance) {
            return $this->response->json([
                'success' => false,
                'message' => 'Attendance record not found'
            ], 404);
        }

        $requestData = $request->all();
        
        $attendance->update($requestData);

        return $this->response->json([
            'success' => true,
            'message' => 'Attendance record updated successfully',
            'data' => $attendance
        ]);
    }

    /**
     * Remove the specified staff attendance record.
     */
    public function destroy(string $id): ResponseInterface
    {
        $attendance = StaffAttendance::find($id);

        if (!$attendance) {
            return $this->response->json([
                'success' => false,
                'message' => 'Attendance record not found'
            ], 404);
        }

        $attendance->delete();

        return $this->response->json([
            'success' => true,
            'message' => 'Attendance record deleted successfully'
        ]);
    }

    /**
     * Mark attendance for a staff member (check-in/check-out).
     */
    public function markAttendance(RequestInterface $request): ResponseInterface
    {
        $requestData = $request->all();
        
        // Basic validation
        if (empty($requestData['staff_id'])) {
            return $this->response->json([
                'success' => false,
                'message' => 'Staff ID is required'
            ], 422);
        }
        
        if (empty($requestData['attendance_date'])) {
            return $this->response->json([
                'success' => false,
                'message' => 'Attendance date is required'
            ], 422);
        }
        
        if (empty($requestData['action'])) {
            return $this->response->json([
                'success' => false,
                'message' => 'Action is required'
            ], 422);
        }
        
        if (empty($requestData['time'])) {
            return $this->response->json([
                'success' => false,
                'message' => 'Time is required'
            ], 422);
        }

        $attendance = StaffAttendance::firstOrCreate(
            [
                'staff_id' => $requestData['staff_id'],
                'attendance_date' => $requestData['attendance_date'],
            ],
            [
                'status' => 'absent', // Default status
            ]
        );

        if ($requestData['action'] === 'check_in') {
            $attendance->update([
                'check_in_time' => $requestData['time'],
                'check_in_method' => 'manual',
                'status' => 'present'
            ]);
        } elseif ($requestData['action'] === 'check_out') {
            $attendance->update([
                'check_out_time' => $requestData['time'],
                'check_out_method' => 'manual'
            ]);
        }

        return $this->response->json([
            'success' => true,
            'message' => 'Attendance marked successfully',
            'data' => $attendance
        ]);
    }
}