<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Attendance\LeaveBalance;
use App\Models\Attendance\LeaveRequest;
use App\Models\Attendance\LeaveType;
use App\Models\SchoolManagement\Staff;
use App\Models\User;
use App\Services\LeaveManagementService;
use Carbon\Carbon;
use Tests\TestCase;

/**
 * @internal
 * @coversNothing
 */
class LeaveManagementServiceTest extends TestCase
{
    private LeaveManagementService $leaveService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->leaveService = new LeaveManagementService();
    }

    public function testCalculateLeaveBalanceCreatesNewBalanceRecord()
    {
        $staff = $this->createStaff();
        $leaveType = $this->createLeaveType();

        $result = $this->leaveService->calculateLeaveBalance($staff->id, $leaveType->id);

        $this->assertArrayHasKey('current_balance', $result);
        $this->assertArrayHasKey('used_days', $result);
        $this->assertArrayHasKey('allocated_days', $result);
        $this->assertArrayHasKey('carry_forward_days', $result);
        $this->assertEquals(0, $result['current_balance']);
        $this->assertEquals(0, $result['used_days']);
        $this->assertEquals(0, $result['allocated_days']);
        $this->assertEquals(0, $result['carry_forward_days']);

        $balance = LeaveBalance::where('staff_id', $staff->id)
            ->where('leave_type_id', $leaveType->id)
            ->where('year', date('Y'))
            ->first();
        $this->assertNotNull($balance);
    }

    public function testCalculateLeaveBalanceReturnsExistingRecord()
    {
        $staff = $this->createStaff();
        $leaveType = $this->createLeaveType();

        LeaveBalance::create([
            'id' => $this->generateUuid(),
            'staff_id' => $staff->id,
            'leave_type_id' => $leaveType->id,
            'year' => date('Y'),
            'allocated_days' => 20,
            'used_days' => 5,
            'carry_forward_days' => 2,
        ]);

        $result = $this->leaveService->calculateLeaveBalance($staff->id, $leaveType->id);

        $this->assertEquals(17, $result['current_balance']);
        $this->assertEquals(5, $result['used_days']);
        $this->assertEquals(20, $result['allocated_days']);
        $this->assertEquals(2, $result['carry_forward_days']);
    }

    public function testUpdateLeaveBalanceOnApproval()
    {
        $staff = $this->createStaff();
        $leaveType = $this->createLeaveType();

        LeaveBalance::create([
            'id' => $this->generateUuid(),
            'staff_id' => $staff->id,
            'leave_type_id' => $leaveType->id,
            'year' => date('Y'),
            'allocated_days' => 20,
            'used_days' => 2,
            'carry_forward_days' => 0,
        ]);

        $leaveRequest = LeaveRequest::create([
            'id' => $this->generateUuid(),
            'staff_id' => $staff->id,
            'leave_type_id' => $leaveType->id,
            'start_date' => Carbon::now()->addDays(5)->toDateString(),
            'end_date' => Carbon::now()->addDays(7)->toDateString(),
            'total_days' => 3,
            'reason' => 'Personal reasons',
            'comments' => null,
            'status' => 'approved',
            'approved_by' => null,
            'approved_at' => null,
            'approval_comments' => null,
            'substitute_assigned_id' => null,
        ]);

        $result = $this->leaveService->updateLeaveBalanceOnApproval($leaveRequest);

        $this->assertTrue($result);

        $balance = LeaveBalance::where('staff_id', $staff->id)
            ->where('leave_type_id', $leaveType->id)
            ->first();
        $this->assertEquals(15, $balance->current_balance);
        $this->assertEquals(5, $balance->used_days);
    }

    public function testValidateLeaveBalanceWithSufficientBalance()
    {
        $staff = $this->createStaff();
        $leaveType = $this->createLeaveType(['requires_approval' => true]);

        LeaveBalance::create([
            'id' => $this->generateUuid(),
            'staff_id' => $staff->id,
            'leave_type_id' => $leaveType->id,
            'year' => date('Y'),
            'allocated_days' => 20,
            'used_days' => 2,
            'carry_forward_days' => 0,
        ]);

        $result = $this->leaveService->validateLeaveBalance($staff->id, $leaveType->id, 5);

        $this->assertTrue($result);
    }

    public function testValidateLeaveBalanceWithInsufficientBalance()
    {
        $staff = $this->createStaff();
        $leaveType = $this->createLeaveType(['requires_approval' => true]);

        LeaveBalance::create([
            'id' => $this->generateUuid(),
            'staff_id' => $staff->id,
            'leave_type_id' => $leaveType->id,
            'year' => date('Y'),
            'allocated_days' => 20,
            'used_days' => 18,
            'carry_forward_days' => 0,
        ]);

        $result = $this->leaveService->validateLeaveBalance($staff->id, $leaveType->id, 5);

        $this->assertFalse($result);
    }

    public function testValidateLeaveBalanceForTypeWithoutApproval()
    {
        $staff = $this->createStaff();
        $leaveType = $this->createLeaveType(['requires_approval' => false]);

        LeaveBalance::create([
            'id' => $this->generateUuid(),
            'staff_id' => $staff->id,
            'leave_type_id' => $leaveType->id,
            'year' => date('Y'),
            'allocated_days' => 0,
            'used_days' => 0,
            'carry_forward_days' => 0,
        ]);

        $result = $this->leaveService->validateLeaveBalance($staff->id, $leaveType->id, 100);

        $this->assertTrue($result);
    }

    public function testValidateLeaveBalanceForNonexistentLeaveType()
    {
        $staff = $this->createStaff();
        $nonExistentLeaveTypeId = '00000000-0000-0000-0000-000000000000';

        $result = $this->leaveService->validateLeaveBalance($staff->id, $nonExistentLeaveTypeId, 5);

        $this->assertTrue($result);
    }

    public function testAllocateAnnualLeave()
    {
        $staff = $this->createStaff();
        $leaveType = $this->createLeaveType();

        $result = $this->leaveService->allocateAnnualLeave($staff->id, $leaveType->id, 15);

        $this->assertTrue($result);

        $balance = LeaveBalance::where('staff_id', $staff->id)
            ->where('leave_type_id', $leaveType->id)
            ->first();
        $this->assertEquals(15, $balance->allocated_days);
        $this->assertEquals(15, $balance->current_balance);
    }

    public function testAllocateAnnualLeaveToExistingBalance()
    {
        $staff = $this->createStaff();
        $leaveType = $this->createLeaveType();

        LeaveBalance::create([
            'id' => $this->generateUuid(),
            'staff_id' => $staff->id,
            'leave_type_id' => $leaveType->id,
            'year' => date('Y'),
            'allocated_days' => 10,
            'used_days' => 3,
            'carry_forward_days' => 0,
        ]);

        $result = $this->leaveService->allocateAnnualLeave($staff->id, $leaveType->id, 5);

        $this->assertTrue($result);

        $balance = LeaveBalance::where('staff_id', $staff->id)
            ->where('leave_type_id', $leaveType->id)
            ->first();
        $this->assertEquals(15, $balance->allocated_days);
        $this->assertEquals(12, $balance->current_balance);
    }

    public function testAllocateAnnualLeaveForSpecificYear()
    {
        $staff = $this->createStaff();
        $leaveType = $this->createLeaveType();
        $specificYear = 2025;

        $result = $this->leaveService->allocateAnnualLeave($staff->id, $leaveType->id, 20, $specificYear);

        $this->assertTrue($result);

        $balance = LeaveBalance::where('staff_id', $staff->id)
            ->where('leave_type_id', $leaveType->id)
            ->where('year', $specificYear)
            ->first();
        $this->assertNotNull($balance);
        $this->assertEquals(20, $balance->allocated_days);
    }

    public function testProcessLeaveCancellationForApprovedRequest()
    {
        $staff = $this->createStaff();
        $leaveType = $this->createLeaveType();

        LeaveBalance::create([
            'id' => $this->generateUuid(),
            'staff_id' => $staff->id,
            'leave_type_id' => $leaveType->id,
            'year' => date('Y'),
            'allocated_days' => 20,
            'used_days' => 10,
            'carry_forward_days' => 0,
        ]);

        $leaveRequest = LeaveRequest::create([
            'id' => $this->generateUuid(),
            'staff_id' => $staff->id,
            'leave_type_id' => $leaveType->id,
            'start_date' => Carbon::now()->addDays(5)->toDateString(),
            'end_date' => Carbon::now()->addDays(9)->toDateString(),
            'total_days' => 5,
            'reason' => 'Personal reasons',
            'comments' => null,
            'status' => 'approved',
            'approved_by' => null,
            'approved_at' => null,
            'approval_comments' => null,
            'substitute_assigned_id' => null,
        ]);

        $result = $this->leaveService->processLeaveCancellation($leaveRequest);

        $this->assertTrue($result);
        $this->assertEquals('cancelled', $leaveRequest->fresh()->status);

        $balance = LeaveBalance::where('staff_id', $staff->id)
            ->where('leave_type_id', $leaveType->id)
            ->first();
        $this->assertEquals(15, $balance->current_balance);
        $this->assertEquals(5, $balance->used_days);
    }

    public function testProcessLeaveCancellationForNonApprovedRequest()
    {
        $staff = $this->createStaff();
        $leaveType = $this->createLeaveType();

        LeaveBalance::create([
            'id' => $this->generateUuid(),
            'staff_id' => $staff->id,
            'leave_type_id' => $leaveType->id,
            'year' => date('Y'),
            'allocated_days' => 20,
            'used_days' => 10,
            'carry_forward_days' => 0,
        ]);

        $leaveRequest = LeaveRequest::create([
            'id' => $this->generateUuid(),
            'staff_id' => $staff->id,
            'leave_type_id' => $leaveType->id,
            'start_date' => Carbon::now()->addDays(5)->toDateString(),
            'end_date' => Carbon::now()->addDays(9)->toDateString(),
            'total_days' => 5,
            'reason' => 'Personal reasons',
            'comments' => null,
            'status' => 'pending',
            'approved_by' => null,
            'approved_at' => null,
            'approval_comments' => null,
            'substitute_assigned_id' => null,
        ]);

        $result = $this->leaveService->processLeaveCancellation($leaveRequest);

        $this->assertFalse($result);
        $this->assertEquals('pending', $leaveRequest->fresh()->status);

        $balance = LeaveBalance::where('staff_id', $staff->id)
            ->where('leave_type_id', $leaveType->id)
            ->first();
        $this->assertEquals(10, $balance->current_balance);
        $this->assertEquals(10, $balance->used_days);
    }

    public function testProcessLeaveCancellationCreatesBalanceIfNotExists()
    {
        $staff = $this->createStaff();
        $leaveType = $this->createLeaveType();

        $leaveRequest = LeaveRequest::create([
            'id' => $this->generateUuid(),
            'staff_id' => $staff->id,
            'leave_type_id' => $leaveType->id,
            'start_date' => Carbon::now()->addDays(5)->toDateString(),
            'end_date' => Carbon::now()->addDays(7)->toDateString(),
            'total_days' => 3,
            'reason' => 'Personal reasons',
            'comments' => null,
            'status' => 'approved',
            'approved_by' => null,
            'approved_at' => null,
            'approval_comments' => null,
            'substitute_assigned_id' => null,
        ]);

        $result = $this->leaveService->processLeaveCancellation($leaveRequest);

        $this->assertTrue($result);

        $balance = LeaveBalance::where('staff_id', $staff->id)
            ->where('leave_type_id', $leaveType->id)
            ->first();
        $this->assertNotNull($balance);
        $this->assertEquals(-3, $balance->current_balance);
        $this->assertEquals(-3, $balance->used_days);
    }

    public function testEdgeCaseZeroBalanceRequest()
    {
        $staff = $this->createStaff();
        $leaveType = $this->createLeaveType();

        LeaveBalance::create([
            'id' => $this->generateUuid(),
            'staff_id' => $staff->id,
            'leave_type_id' => $leaveType->id,
            'year' => date('Y'),
            'allocated_days' => 0,
            'used_days' => 0,
            'carry_forward_days' => 0,
        ]);

        $result = $this->leaveService->validateLeaveBalance($staff->id, $leaveType->id, 1);

        $this->assertFalse($result);
    }

    public function testEdgeCaseExactBalanceAvailable()
    {
        $staff = $this->createStaff();
        $leaveType = $this->createLeaveType();

        LeaveBalance::create([
            'id' => $this->generateUuid(),
            'staff_id' => $staff->id,
            'leave_type_id' => $leaveType->id,
            'year' => date('Y'),
            'allocated_days' => 10,
            'used_days' => 7,
            'carry_forward_days' => 0,
        ]);

        $result = $this->leaveService->validateLeaveBalance($staff->id, $leaveType->id, 3);

        $this->assertTrue($result);
    }

    public function testEdgeCaseNegativeBalanceAfterCancellation()
    {
        $staff = $this->createStaff();
        $leaveType = $this->createLeaveType();

        $leaveRequest = LeaveRequest::create([
            'id' => $this->generateUuid(),
            'staff_id' => $staff->id,
            'leave_type_id' => $leaveType->id,
            'start_date' => Carbon::now()->addDays(5)->toDateString(),
            'end_date' => Carbon::now()->addDays(9)->toDateString(),
            'total_days' => 5,
            'reason' => 'Personal reasons',
            'comments' => null,
            'status' => 'approved',
            'approved_by' => null,
            'approved_at' => null,
            'approval_comments' => null,
            'substitute_assigned_id' => null,
        ]);

        $result = $this->leaveService->processLeaveCancellation($leaveRequest);

        $this->assertTrue($result);

        $balance = LeaveBalance::where('staff_id', $staff->id)
            ->where('leave_type_id', $leaveType->id)
            ->first();
        $this->assertEquals(-5, $balance->current_balance);
        $this->assertEquals(-5, $balance->used_days);
    }

    public function testCarryForwardIncludedInCurrentBalance()
    {
        $staff = $this->createStaff();
        $leaveType = $this->createLeaveType();

        LeaveBalance::create([
            'id' => $this->generateUuid(),
            'staff_id' => $staff->id,
            'leave_type_id' => $leaveType->id,
            'year' => date('Y'),
            'allocated_days' => 20,
            'used_days' => 5,
            'carry_forward_days' => 3,
        ]);

        $result = $this->leaveService->calculateLeaveBalance($staff->id, $leaveType->id);

        $this->assertEquals(18, $result['current_balance']);
        $this->assertEquals(3, $result['carry_forward_days']);
    }

    private function createStaff(): Staff
    {
        $user = User::create([
            'name' => 'Test Staff User ' . uniqid(),
            'email' => 'teststaff' . uniqid() . '@example.com',
            'password' => password_hash('password', PASSWORD_DEFAULT),
            'email_verified_at' => Carbon::now(),
        ]);

        return Staff::create([
            'id' => $this->generateUuid(),
            'user_id' => $user->id,
            'employee_id' => 'EMP' . uniqid(),
            'department' => 'Teaching',
            'position' => 'Teacher',
            'hire_date' => Carbon::now()->subYear()->toDateString(),
            'salary' => 5000000,
            'status' => 'active',
        ]);
    }

    private function createLeaveType(array $overrides = []): LeaveType
    {
        $attributes = array_merge([
            'id' => $this->generateUuid(),
            'name' => 'Annual Leave',
            'description' => 'Annual leave entitlement',
            'requires_approval' => true,
            'days_per_year' => 20,
            'is_paid' => true,
            'carry_forward_allowed' => true,
        ], $overrides);

        return LeaveType::create($attributes);
    }

    private function generateUuid(): string
    {
        $data = random_bytes(16);
        $data[6] = chr(ord($data[6]) & 0x0F | 0x40);
        $data[8] = chr(ord($data[8]) & 0x3F | 0x80);

        return vsprintf('%s%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
    }
}
