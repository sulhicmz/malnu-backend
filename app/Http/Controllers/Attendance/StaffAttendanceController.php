<?php

declare(strict_types=1);

namespace App\Http\Controllers\Attendance;

use App\Http\Controllers\Controller;
use App\Models\Attendance\StaffAttendance;
use App\Models\SchoolManagement\Staff;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class StaffAttendanceController extends Controller
{
    /**
     * Display a listing of the staff attendance records.
     */
    public function index(Request $request): JsonResponse
    {
        $query = StaffAttendance::with('staff');

        // Filter by staff ID if provided
        if ($request->has('staff_id')) {
            $query->where('staff_id', $request->staff_id);
        }

        // Filter by date range if provided
        if ($request->has('start_date') && $request->has('end_date')) {
            $query->whereBetween('attendance_date', [$request->start_date, $request->end_date]);
        } elseif ($request->has('date')) {
            $query->whereDate('attendance_date', $request->date);
        }

        // Filter by status if provided
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        $attendances = $query->orderBy('attendance_date', 'desc')->paginate(15);

        return response()->json([
            'success' => true,
            'data' => $attendances,
        ]);
    }

    /**
     * Store a newly created staff attendance record.
     */
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'staff_id' => 'required|exists:staff,id',
            'attendance_date' => 'required|date',
            'check_in_time' => 'nullable|date_format:H:i',
            'check_out_time' => 'nullable|date_format:H:i|after:check_in_time',
            'status' => 'required|in:present,absent,late,early_departure,on_leave',
            'notes' => 'nullable|string',
            'check_in_method' => 'nullable|string|max:20',
            'check_out_method' => 'nullable|string|max:20',
        ]);

        // Check if attendance record already exists for the same staff and date
        $existingAttendance = StaffAttendance::where('staff_id', $request->staff_id)
            ->whereDate('attendance_date', $request->attendance_date)
            ->first();

        if ($existingAttendance) {
            return response()->json([
                'success' => false,
                'message' => 'Attendance record already exists for this staff on the given date',
            ], 400);
        }

        $attendance = StaffAttendance::create($request->all());

        return response()->json([
            'success' => true,
            'message' => 'Staff attendance recorded successfully',
            'data' => $attendance,
        ], 201);
    }

    /**
     * Display the specified staff attendance record.
     */
    public function show(string $id): JsonResponse
    {
        $attendance = StaffAttendance::with('staff')->find($id);

        if (! $attendance) {
            return response()->json([
                'success' => false,
                'message' => 'Attendance record not found',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $attendance,
        ]);
    }

    /**
     * Update the specified staff attendance record.
     */
    public function update(Request $request, string $id): JsonResponse
    {
        $attendance = StaffAttendance::find($id);

        if (! $attendance) {
            return response()->json([
                'success' => false,
                'message' => 'Attendance record not found',
            ], 404);
        }

        $request->validate([
            'check_in_time' => 'nullable|date_format:H:i',
            'check_out_time' => 'nullable|date_format:H:i|after:check_in_time',
            'status' => 'in:present,absent,late,early_departure,on_leave',
            'notes' => 'nullable|string',
            'check_in_method' => 'nullable|string|max:20',
            'check_out_method' => 'nullable|string|max:20',
        ]);

        $attendance->update($request->all());

        return response()->json([
            'success' => true,
            'message' => 'Attendance record updated successfully',
            'data' => $attendance,
        ]);
    }

    /**
     * Remove the specified staff attendance record.
     */
    public function destroy(string $id): JsonResponse
    {
        $attendance = StaffAttendance::find($id);

        if (! $attendance) {
            return response()->json([
                'success' => false,
                'message' => 'Attendance record not found',
            ], 404);
        }

        $attendance->delete();

        return response()->json([
            'success' => true,
            'message' => 'Attendance record deleted successfully',
        ]);
    }

    /**
     * Mark attendance for a staff member (check-in/check-out).
     */
    public function markAttendance(Request $request): JsonResponse
    {
        $request->validate([
            'staff_id' => 'required|exists:staff,id',
            'attendance_date' => 'required|date',
            'action' => 'required|in:check_in,check_out',
            'time' => 'required|date_format:H:i',
        ]);

        $attendance = StaffAttendance::firstOrCreate(
            [
                'staff_id' => $request->staff_id,
                'attendance_date' => $request->attendance_date,
            ],
            [
                'status' => 'absent', // Default status
            ]
        );

        if ($request->action === 'check_in') {
            $attendance->update([
                'check_in_time' => $request->time,
                'check_in_method' => 'manual',
                'status' => 'present',
            ]);
        } elseif ($request->action === 'check_out') {
            $attendance->update([
                'check_out_time' => $request->time,
                'check_out_method' => 'manual',
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Attendance marked successfully',
            'data' => $attendance,
        ]);
    }
}
