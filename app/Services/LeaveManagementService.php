<?php

namespace App\Services;

use App\Models\Attendance\LeaveRequest;
use App\Models\Attendance\LeaveType;
use App\Models\Attendance\LeaveBalance;
use App\Models\SchoolManagement\Staff;

class LeaveManagementService
{
    /**
     * Calculate leave balance for a staff member and leave type for the current year.
     */
    public function calculateLeaveBalance(string $staffId, string $leaveTypeId): array
    {
        $currentYear = date('Y');
        
        // Get or create leave balance record
        $leaveBalance = LeaveBalance::firstOrCreate(
            [
                'staff_id' => $staffId,
                'leave_type_id' => $leaveTypeId,
                'year' => $currentYear
            ],
            [
                'current_balance' => 0,
                'used_days' => 0,
                'allocated_days' => 0,
                'carry_forward_days' => 0
            ]
        );

        return [
            'current_balance' => $leaveBalance->current_balance,
            'used_days' => $leaveBalance->used_days,
            'allocated_days' => $leaveBalance->allocated_days,
            'carry_forward_days' => $leaveBalance->carry_forward_days
        ];
    }

    /**
     * Update leave balance when a leave request is approved.
     */
    public function updateLeaveBalanceOnApproval(LeaveRequest $leaveRequest): bool
    {
        $leaveBalance = LeaveBalance::firstOrCreate(
            [
                'staff_id' => $leaveRequest->staff_id,
                'leave_type_id' => $leaveRequest->leave_type_id,
                'year' => date('Y')
            ],
            [
                'current_balance' => 0,
                'used_days' => 0,
                'allocated_days' => 0,
                'carry_forward_days' => 0
            ]
        );

        // Update the balance
        $leaveBalance->decrement('current_balance', $leaveRequest->total_days);
        $leaveBalance->increment('used_days', $leaveRequest->total_days);

        return true;
    }

    /**
     * Validate if staff has sufficient leave balance for a leave request.
     */
    public function validateLeaveBalance(string $staffId, string $leaveTypeId, int $requestedDays): bool
    {
        $leaveType = LeaveType::find($leaveTypeId);
        
        // If the leave type doesn't require approval or doesn't have balance tracking, skip validation
        if (!$leaveType || !$leaveType->requires_approval) {
            return true;
        }

        $currentBalance = $this->calculateLeaveBalance($staffId, $leaveTypeId);
        
        return $currentBalance['current_balance'] >= $requestedDays;
    }

    /**
     * Allocate annual leave for a staff member.
     */
    public function allocateAnnualLeave(string $staffId, string $leaveTypeId, int $days, int $year = null): bool
    {
        $year = $year ?: date('Y');
        
        $leaveBalance = LeaveBalance::firstOrCreate(
            [
                'staff_id' => $staffId,
                'leave_type_id' => $leaveTypeId,
                'year' => $year
            ],
            [
                'current_balance' => 0,
                'used_days' => 0,
                'allocated_days' => 0,
                'carry_forward_days' => 0
            ]
        );

        // Calculate new balance
        $newAllocatedDays = $leaveBalance->allocated_days + $days;
        $newCurrentBalance = $leaveBalance->current_balance + $days;
        
        $leaveBalance->update([
            'allocated_days' => $newAllocatedDays,
            'current_balance' => $newCurrentBalance
        ]);

        return true;
    }

    /**
     * Process leave request cancellation.
     */
    public function processLeaveCancellation(LeaveRequest $leaveRequest): bool
    {
        if ($leaveRequest->status !== 'approved') {
            return false; // Can only cancel approved leave
        }

        // Update the leave request status
        $leaveRequest->update(['status' => 'cancelled']);

        // Restore the leave balance
        $leaveBalance = LeaveBalance::firstOrCreate(
            [
                'staff_id' => $leaveRequest->staff_id,
                'leave_type_id' => $leaveRequest->leave_type_id,
                'year' => date('Y')
            ],
            [
                'current_balance' => 0,
                'used_days' => 0,
                'allocated_days' => 0,
                'carry_forward_days' => 0
            ]
        );

        $leaveBalance->increment('current_balance', $leaveRequest->total_days);
        $leaveBalance->decrement('used_days', $leaveRequest->total_days);

        return true;
    }
}