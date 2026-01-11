<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Attendance\LeaveRequest;
use App\Models\Attendance\LeaveType;
use App\Models\SchoolManagement\Staff;
use App\Models\User;
use Tests\TestCase;

/**
 * @internal
 * @coversNothing
 */
class AttendanceLeaveManagementTest extends TestCase
{
    /**
     * Test that leave types can be created and retrieved.
     */
    public function testLeaveTypesCanBeManaged(): void
    {
        // Create a leave type
        $leaveType = LeaveType::create([
            'name' => 'Annual Leave',
            'code' => 'AL',
            'description' => 'Annual vacation leave',
            'max_days_per_year' => 20,
            'is_paid' => true,
            'requires_approval' => true,
            'is_active' => true,
        ]);

        // Verify the leave type was created
        $this->assertNotNull($leaveType->id);
        $this->assertEquals('Annual Leave', $leaveType->name);
        $this->assertEquals('AL', $leaveType->code);

        // Retrieve the leave type
        $retrievedLeaveType = LeaveType::find($leaveType->id);
        $this->assertNotNull($retrievedLeaveType);
        $this->assertEquals($leaveType->id, $retrievedLeaveType->id);
    }

    /**
     * Test that leave requests can be created and managed.
     */
    public function testLeaveRequestsCanBeManaged(): void
    {
        // Create a staff member (using existing user)
        $user = User::factory()->create();
        $staff = Staff::create([
            'user_id' => $user->id,
            'position' => 'Teacher',
            'department' => 'Mathematics',
            'join_date' => '2023-01-01',
        ]);

        // Create a leave type
        $leaveType = LeaveType::create([
            'name' => 'Sick Leave',
            'code' => 'SL',
            'description' => 'Leave for illness',
            'max_days_per_year' => 10,
            'is_paid' => true,
            'requires_approval' => true,
            'is_active' => true,
        ]);

        // Create a leave request
        $leaveRequest = LeaveRequest::create([
            'staff_id' => $staff->id,
            'leave_type_id' => $leaveType->id,
            'start_date' => '2024-06-01',
            'end_date' => '2024-06-03',
            'total_days' => 3,
            'reason' => 'Medical appointment',
            'status' => 'pending',
        ]);

        // Verify the leave request was created
        $this->assertNotNull($leaveRequest->id);
        $this->assertEquals($staff->id, $leaveRequest->staff_id);
        $this->assertEquals($leaveType->id, $leaveRequest->leave_type_id);
        $this->assertEquals('pending', $leaveRequest->status);

        // Retrieve the leave request
        $retrievedLeaveRequest = LeaveRequest::with(['staff', 'leaveType'])->find($leaveRequest->id);
        $this->assertNotNull($retrievedLeaveRequest);
        $this->assertEquals($leaveRequest->id, $retrievedLeaveRequest->id);
        $this->assertNotNull($retrievedLeaveRequest->staff);
        $this->assertNotNull($retrievedLeaveRequest->leaveType);
    }

    /**
     * Test the leave management service functionality.
     */
    public function testLeaveManagementService(): void
    {
        // Create a staff member
        $user = User::factory()->create();
        $staff = Staff::create([
            'user_id' => $user->id,
            'position' => 'Teacher',
            'department' => 'Science',
            'join_date' => '2023-01-01',
        ]);

        // Create a leave type
        $leaveType = LeaveType::create([
            'name' => 'Casual Leave',
            'code' => 'CL',
            'description' => 'Casual leave',
            'max_days_per_year' => 10,
            'is_paid' => false,
            'requires_approval' => true,
            'is_active' => true,
        ]);

        // Test the leave management service
        $service = new \App\Services\LeaveManagementService();

        // Allocate some leave
        $allocationResult = $service->allocateAnnualLeave($staff->id, $leaveType->id, 5);
        $this->assertTrue($allocationResult);

        // Check the balance
        $balance = $service->calculateLeaveBalance($staff->id, $leaveType->id);
        $this->assertEquals(5, $balance['current_balance']);
        $this->assertEquals(5, $balance['allocated_days']);
    }
}
