<?php

namespace Tests\Unit;

use App\Http\Requests\Attendance\ApproveLeaveRequest;
use App\Http\Requests\Attendance\RejectLeaveRequest;
use App\Http\Requests\Attendance\StoreLeaveRequest;
use App\Http\Requests\Attendance\UpdateLeaveRequest;
use App\Http\Requests\SchoolManagement\StoreStudent;
use App\Http\Requests\SchoolManagement\StoreTeacher;
use App\Http\Requests\SchoolManagement\UpdateStudent;
use App\Http\Requests\SchoolManagement\UpdateTeacher;
use Hyperf\HttpServer\Request;
use Tests\TestCase;

class FormRequestValidationTest extends TestCase
{
    public function test_store_leave_request_validation()
    {
        $request = new StoreLeaveRequest();

        $request->merge([
            'staff_id' => 1,
            'leave_type_id' => 1,
            'start_date' => '2024-06-01',
            'end_date' => '2024-06-03',
            'reason' => 'Test reason',
        ]);

        $this->assertTrue($request->authorize());

        $rules = $request->rules();
        $this->assertArrayHasKey('staff_id', $rules);
        $this->assertArrayHasKey('leave_type_id', $rules);
        $this->assertArrayHasKey('start_date', $rules);
        $this->assertArrayHasKey('end_date', $rules);
        $this->assertArrayHasKey('reason', $rules);
    }

    public function test_update_leave_request_validation()
    {
        $request = new UpdateLeaveRequest();

        $request->merge([
            'comments' => 'Test comment',
        ]);

        $this->assertTrue($request->authorize());

        $rules = $request->rules();
        $this->assertArrayHasKey('comments', $rules);
    }

    public function test_approve_leave_request_validation()
    {
        $request = new ApproveLeaveRequest();

        $request->merge([
            'approval_comments' => 'Approved',
        ]);

        $this->assertTrue($request->authorize());

        $rules = $request->rules();
        $this->assertArrayHasKey('approval_comments', $rules);
    }

    public function test_reject_leave_request_validation()
    {
        $request = new RejectLeaveRequest();

        $request->merge([
            'approval_comments' => 'Rejected',
        ]);

        $this->assertTrue($request->authorize());

        $rules = $request->rules();
        $this->assertArrayHasKey('approval_comments', $rules);
    }

    public function test_store_student_validation()
    {
        $request = new StoreStudent();

        $request->merge([
            'name' => 'John Doe',
            'nisn' => '1234567890',
            'email' => 'john@example.com',
            'class_id' => 1,
            'enrollment_year' => 2024,
            'status' => 'active',
        ]);

        $this->assertTrue($request->authorize());

        $rules = $request->rules();
        $this->assertArrayHasKey('name', $rules);
        $this->assertArrayHasKey('nisn', $rules);
        $this->assertArrayHasKey('email', $rules);
        $this->assertArrayHasKey('class_id', $rules);
        $this->assertArrayHasKey('enrollment_year', $rules);
        $this->assertArrayHasKey('status', $rules);
    }

    public function test_update_student_validation()
    {
        $request = new UpdateStudent();

        $request->merge([
            'name' => 'John Updated',
            'email' => 'john.updated@example.com',
        ]);

        $this->assertTrue($request->authorize());

        $rules = $request->rules();
        $this->assertArrayHasKey('name', $rules);
        $this->assertArrayHasKey('nisn', $rules);
        $this->assertArrayHasKey('email', $rules);
        $this->assertArrayHasKey('status', $rules);
    }

    public function test_store_teacher_validation()
    {
        $request = new StoreTeacher();

        $request->merge([
            'name' => 'Jane Smith',
            'nip' => '198506152008011001',
            'email' => 'jane@example.com',
            'subject_id' => 1,
            'join_date' => '2020-01-01',
            'status' => 'active',
        ]);

        $this->assertTrue($request->authorize());

        $rules = $request->rules();
        $this->assertArrayHasKey('name', $rules);
        $this->assertArrayHasKey('nip', $rules);
        $this->assertArrayHasKey('email', $rules);
        $this->assertArrayHasKey('subject_id', $rules);
        $this->assertArrayHasKey('join_date', $rules);
        $this->assertArrayHasKey('status', $rules);
    }

    public function test_update_teacher_validation()
    {
        $request = new UpdateTeacher();

        $request->merge([
            'name' => 'Jane Updated',
            'email' => 'jane.updated@example.com',
        ]);

        $this->assertTrue($request->authorize());

        $rules = $request->rules();
        $this->assertArrayHasKey('name', $rules);
        $this->assertArrayHasKey('nip', $rules);
        $this->assertArrayHasKey('email', $rules);
        $this->assertArrayHasKey('status', $rules);
    }
}
