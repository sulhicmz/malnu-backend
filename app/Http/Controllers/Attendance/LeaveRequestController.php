<?php

namespace App\Http\Controllers\Attendance;

use App\Http\Controllers\Controller;
use App\Models\Attendance\LeaveRequest;
use App\Models\Attendance\LeaveType;
use App\Models\Attendance\LeaveBalance;
use App\Models\SchoolManagement\Staff;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class LeaveRequestController extends Controller
{
    /**
     * Display a listing of the leave requests.
     */
    public function index(Request $request): JsonResponse
    {
        $query = LeaveRequest::with(['staff', 'leaveType', 'approvedBy']);

        // Filter by staff ID if provided
        if ($request->has('staff_id')) {
            $query->where('staff_id', $request->staff_id);
        }

        // Filter by status if provided
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        // Filter by leave type if provided
        if ($request->has('leave_type_id')) {
            $query->where('leave_type_id', $request->leave_type_id);
        }

        // Filter by date range if provided
        if ($request->has('start_date') && $request->has('end_date')) {
            $query->whereBetween('start_date', [$request->start_date, $request->end_date])
                  ->orWhereBetween('end_date', [$request->start_date, $request->end_date]);
        }

        $leaveRequests = $query->orderBy('created_at', 'desc')->paginate(15);

        return response()->json([
            'success' => true,
            'data' => $leaveRequests
        ]);
    }

    /**
     * Store a newly created leave request.
     */
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'staff_id' => 'required|exists:staff,id',
            'leave_type_id' => 'required|exists:leave_types,id',
            'start_date' => 'required|date|before_or_equal:end_date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'reason' => 'required|string|max:500',
            'comments' => 'nullable|string',
        ]);

        // Calculate total days
        $startDate = new \DateTime($request->start_date);
        $endDate = new \DateTime($request->end_date);
        $totalDays = $startDate->diff($endDate)->days + 1; // +1 to include both start and end date

        // Check if staff has sufficient leave balance
        $leaveType = LeaveType::find($request->leave_type_id);
        if ($leaveType && $leaveType->requires_approval) {
            $leaveBalance = LeaveBalance::where('staff_id', $request->staff_id)
                ->where('leave_type_id', $request->leave_type_id)
                ->where('year', date('Y'))
                ->first();

            if ($leaveBalance && $leaveBalance->current_balance < $totalDays) {
                return response()->json([
                    'success' => false,
                    'message' => 'Insufficient leave balance for this request'
                ], 400);
            }
        }

        $leaveRequest = LeaveRequest::create(array_merge(
            $request->all(),
            ['total_days' => $totalDays, 'status' => 'pending']
        ));

        return response()->json([
            'success' => true,
            'message' => 'Leave request submitted successfully',
            'data' => $leaveRequest
        ], 201);
    }

    /**
     * Display the specified leave request.
     */
    public function show(string $id): JsonResponse
    {
        $leaveRequest = LeaveRequest::with(['staff', 'leaveType', 'approvedBy', 'substituteAssignments'])->find($id);

        if (!$leaveRequest) {
            return response()->json([
                'success' => false,
                'message' => 'Leave request not found'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $leaveRequest
        ]);
    }

    /**
     * Update the specified leave request.
     */
    public function update(Request $request, string $id): JsonResponse
    {
        $leaveRequest = LeaveRequest::find($id);

        if (!$leaveRequest) {
            return response()->json([
                'success' => false,
                'message' => 'Leave request not found'
            ], 404);
        }

        // Only allow updates to comments and status if not approved/rejected yet
        if ($leaveRequest->status !== 'pending') {
            return response()->json([
                'success' => false,
                'message' => 'Cannot update leave request that is already processed'
            ], 400);
        }

        $request->validate([
            'comments' => 'nullable|string',
        ]);

        $leaveRequest->update($request->only(['comments']));

        return response()->json([
            'success' => true,
            'message' => 'Leave request updated successfully',
            'data' => $leaveRequest
        ]);
    }

    /**
     * Remove the specified leave request.
     */
    public function destroy(string $id): JsonResponse
    {
        $leaveRequest = LeaveRequest::find($id);

        if (!$leaveRequest) {
            return response()->json([
                'success' => false,
                'message' => 'Leave request not found'
            ], 404);
        }

        // Only allow deletion if status is pending
        if ($leaveRequest->status !== 'pending') {
            return response()->json([
                'success' => false,
                'message' => 'Cannot delete leave request that is already processed'
            ], 400);
        }

        $leaveRequest->delete();

        return response()->json([
            'success' => true,
            'message' => 'Leave request deleted successfully'
        ]);
    }

    /**
     * Approve a leave request.
     */
    public function approve(Request $request, string $id): JsonResponse
    {
        $leaveRequest = LeaveRequest::find($id);

        if (!$leaveRequest) {
            return response()->json([
                'success' => false,
                'message' => 'Leave request not found'
            ], 404);
        }

        if ($leaveRequest->status !== 'pending') {
            return response()->json([
                'success' => false,
                'message' => 'Leave request is not in pending status'
            ], 400);
        }

        $leaveRequest->update([
            'status' => 'approved',
            'approved_by' => $request->user()->id ?? null, // Assuming user authentication
            'approved_at' => now(),
            'approval_comments' => $request->approval_comments
        ]);

        // Update leave balance if applicable
        $leaveBalance = LeaveBalance::where('staff_id', $leaveRequest->staff_id)
            ->where('leave_type_id', $leaveRequest->leave_type_id)
            ->where('year', date('Y'))
            ->first();

        if ($leaveBalance) {
            $leaveBalance->decrement('current_balance', $leaveRequest->total_days);
            $leaveBalance->increment('used_days', $leaveRequest->total_days);
        }

        return response()->json([
            'success' => true,
            'message' => 'Leave request approved successfully',
            'data' => $leaveRequest
        ]);
    }

    /**
     * Reject a leave request.
     */
    public function reject(Request $request, string $id): JsonResponse
    {
        $leaveRequest = LeaveRequest::find($id);

        if (!$leaveRequest) {
            return response()->json([
                'success' => false,
                'message' => 'Leave request not found'
            ], 404);
        }

        if ($leaveRequest->status !== 'pending') {
            return response()->json([
                'success' => false,
                'message' => 'Leave request is not in pending status'
            ], 400);
        }

        $leaveRequest->update([
            'status' => 'rejected',
            'approved_by' => $request->user()->id ?? null, // Assuming user authentication
            'approved_at' => now(),
            'approval_comments' => $request->approval_comments
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Leave request rejected successfully',
            'data' => $leaveRequest
        ]);
    }
}