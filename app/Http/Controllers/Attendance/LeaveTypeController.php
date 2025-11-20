<?php

namespace App\Http\Controllers\Attendance;

use App\Http\Controllers\Controller;
use App\Models\Attendance\LeaveType;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class LeaveTypeController extends Controller
{
    /**
     * Display a listing of the leave types.
     */
    public function index(Request $request): JsonResponse
    {
        $query = LeaveType::query();

        // Filter by active status if provided
        if ($request->has('is_active')) {
            $query->where('is_active', $request->is_active);
        }

        // Filter by paid status if provided
        if ($request->has('is_paid')) {
            $query->where('is_paid', $request->is_paid);
        }

        $leaveTypes = $query->orderBy('name')->paginate(15);

        return response()->json([
            'success' => true,
            'data' => $leaveTypes
        ]);
    }

    /**
     * Store a newly created leave type.
     */
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'name' => 'required|string|max:100',
            'code' => 'required|string|max:20|unique:leave_types,code',
            'description' => 'nullable|string',
            'max_days_per_year' => 'nullable|integer|min:0',
            'is_paid' => 'boolean',
            'requires_approval' => 'boolean',
            'eligibility_criteria' => 'nullable|json',
            'is_active' => 'boolean',
        ]);

        $leaveType = LeaveType::create($request->all());

        return response()->json([
            'success' => true,
            'message' => 'Leave type created successfully',
            'data' => $leaveType
        ], 201);
    }

    /**
     * Display the specified leave type.
     */
    public function show(string $id): JsonResponse
    {
        $leaveType = LeaveType::find($id);

        if (!$leaveType) {
            return response()->json([
                'success' => false,
                'message' => 'Leave type not found'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $leaveType
        ]);
    }

    /**
     * Update the specified leave type.
     */
    public function update(Request $request, string $id): JsonResponse
    {
        $leaveType = LeaveType::find($id);

        if (!$leaveType) {
            return response()->json([
                'success' => false,
                'message' => 'Leave type not found'
            ], 404);
        }

        $request->validate([
            'name' => 'string|max:100',
            'code' => 'string|max:20|unique:leave_types,code,' . $id,
            'description' => 'nullable|string',
            'max_days_per_year' => 'nullable|integer|min:0',
            'is_paid' => 'boolean',
            'requires_approval' => 'boolean',
            'eligibility_criteria' => 'nullable|json',
            'is_active' => 'boolean',
        ]);

        $leaveType->update($request->all());

        return response()->json([
            'success' => true,
            'message' => 'Leave type updated successfully',
            'data' => $leaveType
        ]);
    }

    /**
     * Remove the specified leave type.
     */
    public function destroy(string $id): JsonResponse
    {
        $leaveType = LeaveType::find($id);

        if (!$leaveType) {
            return response()->json([
                'success' => false,
                'message' => 'Leave type not found'
            ], 404);
        }

        // Check if there are any leave requests associated with this type
        if ($leaveType->leaveRequests()->count() > 0) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot delete leave type with associated leave requests'
            ], 400);
        }

        $leaveType->delete();

        return response()->json([
            'success' => true,
            'message' => 'Leave type deleted successfully'
        ]);
    }
}