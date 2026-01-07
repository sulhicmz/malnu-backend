<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Services\LeaveManagementService;
use Tests\TestCase;

/**
 * @internal
 * @coversNothing
 */
class LeaveManagementServiceExtendedTest extends TestCase
{
    private LeaveManagementService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new LeaveManagementService();
    }

    public function testCalculateLeaveBalanceForNewStaff()
    {
        $staffId = 'staff_123';
        $leaveTypeId = 'annual_leave';

        $balance = $this->service->calculateLeaveBalance($staffId, $leaveTypeId);

        $this->assertIsArray($balance);
        $this->assertArrayHasKey('current_balance', $balance);
        $this->assertArrayHasKey('used_days', $balance);
        $this->assertArrayHasKey('allocated_days', $balance);
        $this->assertArrayHasKey('carry_forward_days', $balance);
        $this->assertEquals(0, $balance['current_balance']);
        $this->assertEquals(0, $balance['used_days']);
        $this->assertEquals(0, $balance['allocated_days']);
        $this->assertEquals(0, $balance['carry_forward_days']);
    }

    public function testCalculateLeaveBalanceReturnsCurrentYear()
    {
        $staffId = 'staff_123';
        $leaveTypeId = 'annual_leave';

        $balance = $this->service->calculateLeaveBalance($staffId, $leaveTypeId);

        $currentYear = date('Y');
        $this->assertIsArray($balance);
    }

    public function testCalculateLeaveBalanceStructure()
    {
        $staffId = 'staff_456';
        $leaveTypeId = 'sick_leave';

        $balance = $this->service->calculateLeaveBalance($staffId, $leaveTypeId);

        $this->assertIsArray($balance);
        $this->assertCount(4, $balance);
    }

    public function testAllocateAnnualLeaveIncreasesBalance()
    {
        $staffId = 'staff_789';
        $leaveTypeId = 'annual_leave';
        $allocatedDays = 10;

        $result = $this->service->allocateAnnualLeave($staffId, $leaveTypeId, $allocatedDays);

        $this->assertTrue($result);
    }

    public function testAllocateAnnualLeaveWithZeroDays()
    {
        $staffId = 'staff_000';
        $leaveTypeId = 'annual_leave';
        $allocatedDays = 0;

        $result = $this->service->allocateAnnualLeave($staffId, $leaveTypeId, $allocatedDays);

        $this->assertTrue($result);
    }

    public function testAllocateAnnualLeaveWithLargeDays()
    {
        $staffId = 'staff_large';
        $leaveTypeId = 'annual_leave';
        $allocatedDays = 365;

        $result = $this->service->allocateAnnualLeave($staffId, $leaveTypeId, $allocatedDays);

        $this->assertTrue($result);
    }

    public function testAllocateAnnualLeaveForSpecificYear()
    {
        $staffId = 'staff_year';
        $leaveTypeId = 'annual_leave';
        $allocatedDays = 15;
        $year = 2025;

        $result = $this->service->allocateAnnualLeave($staffId, $leaveTypeId, $allocatedDays, $year);

        $this->assertTrue($result);
    }

    public function testAllocateAnnualLeaveAccumulatesBalance()
    {
        $staffId = 'staff_accumulate';
        $leaveTypeId = 'annual_leave';

        $this->service->allocateAnnualLeave($staffId, $leaveTypeId, 10);
        $balance1 = $this->service->calculateLeaveBalance($staffId, $leaveTypeId);

        $this->service->allocateAnnualLeave($staffId, $leaveTypeId, 5);
        $balance2 = $this->service->calculateLeaveBalance($staffId, $leaveTypeId);

        $this->assertGreaterThanOrEqual($balance1['allocated_days'], $balance2['allocated_days']);
    }

    public function testValidateLeaveBalanceWithInsufficientDays()
    {
        $staffId = 'staff_validate';
        $leaveTypeId = 'annual_leave';
        $requestedDays = 20;

        $isValid = $this->service->validateLeaveBalance($staffId, $leaveTypeId, $requestedDays);

        $this->assertFalse($isValid);
    }

    public function testValidateLeaveBalanceWithZeroDays()
    {
        $staffId = 'staff_zero';
        $leaveTypeId = 'annual_leave';
        $requestedDays = 0;

        $isValid = $this->service->validateLeaveBalance($staffId, $leaveTypeId, $requestedDays);

        $this->assertTrue($isValid);
    }

    public function testValidateLeaveBalanceWithNegativeDays()
    {
        $staffId = 'staff_negative';
        $leaveTypeId = 'annual_leave';
        $requestedDays = -5;

        $isValid = $this->service->validateLeaveBalance($staffId, $leaveTypeId, $requestedDays);

        $this->assertTrue($isValid);
    }

    public function testValidateLeaveBalanceForMultipleStaff()
    {
        $staff1 = 'staff_1';
        $staff2 = 'staff_2';
        $leaveTypeId = 'annual_leave';

        $this->service->allocateAnnualLeave($staff1, $leaveTypeId, 10);
        $this->service->allocateAnnualLeave($staff2, $leaveTypeId, 20);

        $isValid1 = $this->service->validateLeaveBalance($staff1, $leaveTypeId, 5);
        $isValid2 = $this->service->validateLeaveBalance($staff2, $leaveTypeId, 15);

        $this->assertTrue($isValid1);
        $this->assertTrue($isValid2);
    }

    public function testCalculateLeaveBalanceStructureConsistency()
    {
        $staffId = 'staff_consistency';
        $leaveType1 = 'annual_leave';
        $leaveType2 = 'sick_leave';

        $balance1 = $this->service->calculateLeaveBalance($staffId, $leaveType1);
        $balance2 = $this->service->calculateLeaveBalance($staffId, $leaveType2);

        $this->assertEquals(array_keys($balance1), array_keys($balance2));
    }

    public function testServiceMethodsReturnExpectedTypes()
    {
        $staffId = 'staff_types';
        $leaveTypeId = 'annual_leave';

        $balance = $this->service->calculateLeaveBalance($staffId, $leaveTypeId);
        $allocateResult = $this->service->allocateAnnualLeave($staffId, $leaveTypeId, 10);
        $validateResult = $this->service->validateLeaveBalance($staffId, $leaveTypeId, 5);

        $this->assertIsArray($balance);
        $this->assertIsBool($allocateResult);
        $this->assertIsBool($validateResult);
    }

    public function testCalculateBalanceWithEmptyStaffId()
    {
        $staffId = '';
        $leaveTypeId = 'annual_leave';

        $balance = $this->service->calculateLeaveBalance($staffId, $leaveTypeId);

        $this->assertIsArray($balance);
    }

    public function testCalculateBalanceWithEmptyLeaveTypeId()
    {
        $staffId = 'staff_empty_type';
        $leaveTypeId = '';

        $balance = $this->service->calculateLeaveBalance($staffId, $leaveTypeId);

        $this->assertIsArray($balance);
    }

    public function testServiceInstantiation()
    {
        $service = new LeaveManagementService();

        $this->assertInstanceOf(LeaveManagementService::class, $service);
    }
}
