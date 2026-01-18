<?php

namespace Tests\Feature;

use App\Models\Attendance\LeaveBalance;
use App\Models\Attendance\SubstituteTeacher;
use App\Models\Attendance\SubstituteAssignment;
use App\Models\Attendance\LeaveRequest;
use App\Models\Attendance\LeaveType;
use App\Models\SchoolManagement\Staff;
use App\Models\User;
use Tests\TestCase;

class SubstituteManagementTest extends TestCase
{
    public function test_substitute_teacher_can_be_created(): void
    {
        $user = User::factory()->create();
        $staff = Staff::create([
            'user_id' => $user->id,
            'position' => 'Teacher',
            'department' => 'Mathematics',
        ]);

        $substituteTeacher = SubstituteTeacher::create([
            'teacher_id' => $staff->id,
            'is_active' => true,
            'available_subjects' => ['Mathematics', 'Physics'],
            'available_classes' => ['Class 10A', 'Class 10B'],
            'hourly_rate' => 50.00,
        ]);

        $this->assertNotNull($substituteTeacher->id);
        $this->assertTrue($substituteTeacher->is_active);
        $this->assertEquals(50.00, $substituteTeacher->hourly_rate);
    }

    public function test_substitute_assignment_can_be_created(): void
    {
        $user = User::factory()->create();
        $staff = Staff::create([
            'user_id' => $user->id,
            'position' => 'Teacher',
            'department' => 'Mathematics',
        ]);

        $substituteTeacher = SubstituteTeacher::create([
            'teacher_id' => $staff->id,
            'is_active' => true,
            'hourly_rate' => 50.00,
        ]);

        $leaveType = LeaveType::create([
            'name' => 'Sick Leave',
            'code' => 'SL',
            'max_days_per_year' => 10,
            'is_paid' => true,
            'requires_approval' => true,
            'is_active' => true,
        ]);

        $leaveRequest = LeaveRequest::create([
            'staff_id' => $staff->id,
            'leave_type_id' => $leaveType->id,
            'start_date' => '2026-02-01',
            'end_date' => '2026-02-03',
            'total_days' => 3,
            'reason' => 'Medical appointment',
            'status' => 'approved',
        ]);

        $assignment = SubstituteAssignment::create([
            'leave_request_id' => $leaveRequest->id,
            'substitute_teacher_id' => $substituteTeacher->id,
            'assignment_date' => '2026-02-01',
            'status' => 'pending',
            'payment_amount' => 400.00,
        ]);

        $this->assertNotNull($assignment->id);
        $this->assertEquals('pending', $assignment->status);
        $this->assertEquals(400.00, $assignment->payment_amount);
    }

    public function test_leave_balance_can_be_created(): void
    {
        $user = User::factory()->create();
        $staff = Staff::create([
            'user_id' => $user->id,
            'position' => 'Teacher',
            'department' => 'Mathematics',
        ]);

        $leaveType = LeaveType::create([
            'name' => 'Annual Leave',
            'code' => 'AL',
            'max_days_per_year' => 20,
            'is_paid' => true,
            'requires_approval' => true,
            'is_active' => true,
        ]);

        $leaveBalance = LeaveBalance::create([
            'staff_id' => $staff->id,
            'leave_type_id' => $leaveType->id,
            'current_balance' => 20,
            'used_days' => 5,
            'allocated_days' => 20,
            'carry_forward_days' => 0,
            'year' => 2026,
        ]);

        $this->assertNotNull($leaveBalance->id);
        $this->assertEquals(20, $leaveBalance->current_balance);
        $this->assertEquals(5, $leaveBalance->used_days);
        $this->assertEquals(2026, $leaveBalance->year);
    }

    public function test_leave_balance_adjustment_adds_days(): void
    {
        $user = User::factory()->create();
        $staff = Staff::create([
            'user_id' => $user->id,
            'position' => 'Teacher',
            'department' => 'Mathematics',
        ]);

        $leaveType = LeaveType::create([
            'name' => 'Annual Leave',
            'code' => 'AL',
            'max_days_per_year' => 20,
            'is_paid' => true,
            'requires_approval' => true,
            'is_active' => true,
        ]);

        $leaveBalance = LeaveBalance::create([
            'staff_id' => $staff->id,
            'leave_type_id' => $leaveType->id,
            'current_balance' => 10,
            'used_days' => 5,
            'allocated_days' => 15,
            'carry_forward_days' => 0,
            'year' => 2026,
        ]);

        $initialBalance = $leaveBalance->current_balance;

        $leaveBalance->increment('current_balance', 5);
        $leaveBalance->increment('allocated_days', 5);

        $leaveBalance->refresh();

        $this->assertEquals($initialBalance + 5, $leaveBalance->current_balance);
        $this->assertEquals(20, $leaveBalance->allocated_days);
    }

    public function test_leave_balance_adjustment_subtracts_days(): void
    {
        $user = User::factory()->create();
        $staff = Staff::create([
            'user_id' => $user->id,
            'position' => 'Teacher',
            'department' => 'Mathematics',
        ]);

        $leaveType = LeaveType::create([
            'name' => 'Annual Leave',
            'code' => 'AL',
            'max_days_per_year' => 20,
            'is_paid' => true,
            'requires_approval' => true,
            'is_active' => true,
        ]);

        $leaveBalance = LeaveBalance::create([
            'staff_id' => $staff->id,
            'leave_type_id' => $leaveType->id,
            'current_balance' => 10,
            'used_days' => 5,
            'allocated_days' => 15,
            'carry_forward_days' => 0,
            'year' => 2026,
        ]);

        $initialBalance = $leaveBalance->current_balance;

        $leaveBalance->decrement('current_balance', 3);
        $leaveBalance->increment('used_days', 3);

        $leaveBalance->refresh();

        $this->assertEquals($initialBalance - 3, $leaveBalance->current_balance);
        $this->assertEquals(8, $leaveBalance->used_days);
    }

    public function test_substitute_teacher_relationships_work_correctly(): void
    {
        $user = User::factory()->create();
        $staff = Staff::create([
            'user_id' => $user->id,
            'position' => 'Teacher',
            'department' => 'Mathematics',
        ]);

        $substituteTeacher = SubstituteTeacher::create([
            'teacher_id' => $staff->id,
            'is_active' => true,
            'hourly_rate' => 50.00,
        ]);

        $loadedSubstituteTeacher = SubstituteTeacher::with('teacher')->find($substituteTeacher->id);

        $this->assertNotNull($loadedSubstituteTeacher->teacher);
        $this->assertEquals($staff->id, $loadedSubstituteTeacher->teacher->id);
    }

    public function test_substitute_assignment_relationships_work_correctly(): void
    {
        $user = User::factory()->create();
        $staff = Staff::create([
            'user_id' => $user->id,
            'position' => 'Teacher',
            'department' => 'Mathematics',
        ]);

        $substituteTeacher = SubstituteTeacher::create([
            'teacher_id' => $staff->id,
            'is_active' => true,
            'hourly_rate' => 50.00,
        ]);

        $leaveType = LeaveType::create([
            'name' => 'Sick Leave',
            'code' => 'SL',
            'max_days_per_year' => 10,
            'is_paid' => true,
            'requires_approval' => true,
            'is_active' => true,
        ]);

        $leaveRequest = LeaveRequest::create([
            'staff_id' => $staff->id,
            'leave_type_id' => $leaveType->id,
            'start_date' => '2026-02-01',
            'end_date' => '2026-02-03',
            'total_days' => 3,
            'reason' => 'Medical appointment',
            'status' => 'approved',
        ]);

        $assignment = SubstituteAssignment::create([
            'leave_request_id' => $leaveRequest->id,
            'substitute_teacher_id' => $substituteTeacher->id,
            'assignment_date' => '2026-02-01',
            'status' => 'completed',
            'payment_amount' => 1200.00,
        ]);

        $loadedAssignment = SubstituteAssignment::with([
            'leaveRequest',
            'substituteTeacher',
            'classSubject'
        ])->find($assignment->id);

        $this->assertNotNull($loadedAssignment->leaveRequest);
        $this->assertNotNull($loadedAssignment->substituteTeacher);
        $this->assertEquals($leaveRequest->id, $loadedAssignment->leaveRequest->id);
        $this->assertEquals($substituteTeacher->id, $loadedAssignment->substituteTeacher->id);
    }

    public function test_leave_balance_relationships_work_correctly(): void
    {
        $user = User::factory()->create();
        $staff = Staff::create([
            'user_id' => $user->id,
            'position' => 'Teacher',
            'department' => 'Mathematics',
        ]);

        $leaveType = LeaveType::create([
            'name' => 'Annual Leave',
            'code' => 'AL',
            'max_days_per_year' => 20,
            'is_paid' => true,
            'requires_approval' => true,
            'is_active' => true,
        ]);

        $leaveBalance = LeaveBalance::create([
            'staff_id' => $staff->id,
            'leave_type_id' => $leaveType->id,
            'current_balance' => 10,
            'used_days' => 5,
            'allocated_days' => 15,
            'carry_forward_days' => 0,
            'year' => 2026,
        ]);

        $loadedLeaveBalance = LeaveBalance::with(['staff', 'leaveType'])->find($leaveBalance->id);

        $this->assertNotNull($loadedLeaveBalance->staff);
        $this->assertNotNull($loadedLeaveBalance->leaveType);
        $this->assertEquals($staff->id, $loadedLeaveBalance->staff->id);
        $this->assertEquals($leaveType->id, $loadedLeaveBalance->leaveType->id);
    }
}
