<?php

declare(strict_types=1);

namespace App\Contracts;

use App\Models\Attendance\LeaveRequest;

interface LeaveManagementServiceInterface
{
    public function calculateLeaveBalance(string $staffId, string $leaveTypeId): array;

    public function updateLeaveBalanceOnApproval(LeaveRequest $leaveRequest): bool;

    public function validateLeaveBalance(string $staffId, string $leaveTypeId, int $requestedDays): bool;

    public function allocateAnnualLeave(string $staffId, string $leaveTypeId, int $days, ?int $year = null): bool;

    public function processLeaveCancellation(LeaveRequest $leaveRequest): bool;
}
