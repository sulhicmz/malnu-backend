<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Attendance\LeaveBalance;
use App\Models\Attendance\LeaveRequest;
use App\Models\Attendance\LeaveType;
use App\Models\SchoolManagement\Staff;
use Psr\Container\ContainerInterface;

class LeaveManagementService
{
    private CacheService $cacheService;

    public function __construct(ContainerInterface $container)
    {
        $this->cacheService = $container->get(CacheService::class);
    }

    /**
     * Calculate leave balance for a staff member and leave type for the current year.
     */
    public function calculateLeaveBalance(string $staffId, string $leaveTypeId): array
    {
        $currentYear = date('Y');

        $cacheKey = $this->cacheService->generateKey('leave_balance', [
            'staff_id' => $staffId,
            'leave_type_id' => $leaveTypeId,
            'year' => $currentYear,
        ]);

        return $this->cacheService->remember($cacheKey, function () use ($staffId, $leaveTypeId, $currentYear) {
            $leaveBalance = LeaveBalance::firstOrCreate(
                [
                    'staff_id' => $staffId,
                    'leave_type_id' => $leaveTypeId,
                    'year' => $currentYear,
                ],
                [
                    'current_balance' => 0,
                    'used_days' => 0,
                    'allocated_days' => 0,
                    'carry_forward_days' => 0,
                ]
            );

            return [
                'current_balance' => $leaveBalance->current_balance,
                'used_days' => $leaveBalance->used_days,
                'allocated_days' => $leaveBalance->allocated_days,
                'carry_forward_days' => $leaveBalance->carry_forward_days,
            ];
        }, 3600);
    }

    /**
     * Update leave balance when a leave request is approved.
     */
    public function updateLeaveBalanceOnApproval(LeaveRequest $leaveRequest): bool
    {
        $currentYear = date('Y');
        $cacheKey = $this->cacheService->generateKey('leave_balance', [
            'staff_id' => $leaveRequest->staff_id,
            'leave_type_id' => $leaveRequest->leave_type_id,
            'year' => $currentYear,
        ]);

        $leaveBalance = LeaveBalance::firstOrCreate(
            [
                'staff_id' => $leaveRequest->staff_id,
                'leave_type_id' => $leaveRequest->leave_type_id,
                'year' => $currentYear,
            ],
            [
                'current_balance' => 0,
                'used_days' => 0,
                'allocated_days' => 0,
                'carry_forward_days' => 0,
            ]
        );

        $leaveBalance->decrement('current_balance', $leaveRequest->total_days);
        $leaveBalance->increment('used_days', $leaveRequest->total_days);

        $this->cacheService->forget($cacheKey);

        return true;
    }

    /**
     * Validate if staff has sufficient leave balance for a leave request.
     */
    public function validateLeaveBalance(string $staffId, string $leaveTypeId, int $requestedDays): bool
    {
        $leaveType = LeaveType::find($leaveTypeId);

        if (! $leaveType || ! $leaveType->requires_approval) {
            return true;
        }

        $currentBalance = $this->calculateLeaveBalance($staffId, $leaveTypeId);

        return $currentBalance['current_balance'] >= $requestedDays;
    }

    /**
     * Allocate annual leave for a staff member.
     */
    public function allocateAnnualLeave(string $staffId, string $leaveTypeId, int $days, ?int $year = null): bool
    {
        $year = $year ?: date('Y');

        $cacheKey = $this->cacheService->generateKey('leave_balance', [
            'staff_id' => $staffId,
            'leave_type_id' => $leaveTypeId,
            'year' => $year,
        ]);

        $leaveBalance = LeaveBalance::firstOrCreate(
            [
                'staff_id' => $staffId,
                'leave_type_id' => $leaveTypeId,
                'year' => $year,
            ],
            [
                'current_balance' => 0,
                'used_days' => 0,
                'allocated_days' => 0,
                'carry_forward_days' => 0,
            ]
        );

        $newAllocatedDays = $leaveBalance->allocated_days + $days;
        $newCurrentBalance = $leaveBalance->current_balance + $days;

        $leaveBalance->update([
            'allocated_days' => $newAllocatedDays,
            'current_balance' => $newCurrentBalance,
        ]);

        $this->cacheService->forget($cacheKey);

        return true;
    }

    /**
     * Process leave request cancellation.
     */
    public function processLeaveCancellation(LeaveRequest $leaveRequest): bool
    {
        if ($leaveRequest->status !== 'approved') {
            return false;
        }

        $currentYear = date('Y');

        $cacheKey = $this->cacheService->generateKey('leave_balance', [
            'staff_id' => $leaveRequest->staff_id,
            'leave_type_id' => $leaveRequest->leave_type_id,
            'year' => $currentYear,
        ]);

        $leaveRequest->update(['status' => 'cancelled']);

        $leaveBalance = LeaveBalance::firstOrCreate(
            [
                'staff_id' => $leaveRequest->staff_id,
                'leave_type_id' => $leaveRequest->leave_type_id,
                'year' => $currentYear,
            ],
            [
                'current_balance' => 0,
                'used_days' => 0,
                'allocated_days' => 0,
                'carry_forward_days' => 0,
            ]
        );

        $leaveBalance->increment('current_balance', $leaveRequest->total_days);
        $leaveBalance->decrement('used_days', $leaveRequest->total_days);

        $this->cacheService->forget($cacheKey);

        return true;
    }
}
