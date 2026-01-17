<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Http\Requests\Attendance\StoreLeaveRequest;
use App\Http\Requests\Attendance\UpdateLeaveRequest;
use App\Http\Requests\Attendance\ApproveLeaveRequest;
use App\Http\Requests\Attendance\RejectLeaveRequest;
use App\Http\Requests\SchoolManagement\StoreStudent;
use App\Http\Requests\SchoolManagement\UpdateStudent;
use App\Http\Requests\SchoolManagement\StoreTeacher;
use App\Http\Requests\SchoolManagement\UpdateTeacher;
use Hyperf\HttpServer\Request;
use Hyperf\Context\ApplicationContext;

class FormRequestValidationTest extends TestCase
{
    public function test_store_leave_request_authorization()
    {
        $request = new StoreLeaveRequest();
        $this->assertTrue($request->authorize());
    }

    public function test_store_leave_request_validation_rules()
    {
        $request = new StoreLeaveRequest();
        $rules = $request->rules();

        $this->assertArrayHasKey('staff_id', $rules);
        $this->assertArrayHasKey('leave_type_id', $rules);
        $this->assertArrayHasKey('start_date', $rules);
        $this->assertArrayHasKey('end_date', $rules);
        $this->assertArrayHasKey('reason', $rules);
    }

    public function test_update_leave_request_authorization()
    {
        $request = new UpdateLeaveRequest();
        $this->assertTrue($request->authorize());
    }

    public function test_update_leave_request_validation_rules()
    {
        $request = new UpdateLeaveRequest();
        $rules = $request->rules();

        $this->assertArrayHasKey('comments', $rules);
        $this->assertStringContainsString('sometimes', $rules['comments']);
        $this->assertStringContainsString('string', $rules['comments']);
    }

    public function test_approve_leave_request_authorization()
    {
        $request = new ApproveLeaveRequest();
        $this->assertTrue($request->authorize());
    }

    public function test_approve_leave_request_validation_rules()
    {
        $request = new ApproveLeaveRequest();
        $rules = $request->rules();

        $this->assertArrayHasKey('approval_comments', $rules);
        $this->assertStringContainsString('nullable', $rules['approval_comments']);
        $this->assertStringContainsString('string', $rules['approval_comments']);
    }

    public function test_reject_leave_request_authorization()
    {
        $request = new RejectLeaveRequest();
        $this->assertTrue($request->authorize());
    }

    public function test_reject_leave_request_validation_rules()
    {
        $request = new RejectLeaveRequest();
        $rules = $request->rules();

        $this->assertArrayHasKey('approval_comments', $rules);
        $this->assertStringContainsString('required', $rules['approval_comments']);
        $this->assertStringContainsString('string', $rules['approval_comments']);
    }

    public function test_store_student_authorization()
    {
        $request = new StoreStudent();
        $this->assertTrue($request->authorize());
    }

    public function test_store_student_validation_rules()
    {
        $request = new StoreStudent();
        $rules = $request->rules();

        $this->assertArrayHasKey('name', $rules);
        $this->assertArrayHasKey('nisn', $rules);
        $this->assertArrayHasKey('email', $rules);
        $this->assertArrayHasKey('class_id', $rules);
        $this->assertArrayHasKey('enrollment_year', $rules);
        $this->assertArrayHasKey('status', $rules);

        $this->assertStringContainsString('required', $rules['name']);
        $this->assertStringContainsString('required', $rules['nisn']);
        $this->assertStringContainsString('unique', $rules['nisn']);
        $this->assertStringContainsString('email', $rules['email']);
        $this->assertStringContainsString('unique', $rules['email']);
    }

    public function test_update_student_authorization()
    {
        $request = new UpdateStudent();
        $this->assertTrue($request->authorize());
    }

    public function test_update_student_validation_rules()
    {
        $request = new UpdateStudent();
        $rules = $request->rules();

        $this->assertArrayHasKey('name', $rules);
        $this->assertArrayHasKey('nisn', $rules);
        $this->assertArrayHasKey('email', $rules);
        $this->assertArrayHasKey('status', $rules);

        $this->assertStringContainsString('sometimes', $rules['name']);
        $this->assertStringContainsString('sometimes', $rules['nisn']);
        $this->assertStringContainsString('unique', $rules['nisn']);
    }

    public function test_store_teacher_authorization()
    {
        $request = new StoreTeacher();
        $this->assertTrue($request->authorize());
    }

    public function test_store_teacher_validation_rules()
    {
        $request = new StoreTeacher();
        $rules = $request->rules();

        $this->assertArrayHasKey('name', $rules);
        $this->assertArrayHasKey('nip', $rules);
        $this->assertArrayHasKey('email', $rules);
        $this->assertArrayHasKey('subject_id', $rules);
        $this->assertArrayHasKey('join_date', $rules);
        $this->assertArrayHasKey('status', $rules);

        $this->assertStringContainsString('required', $rules['name']);
        $this->assertStringContainsString('required', $rules['nip']);
        $this->assertStringContainsString('unique', $rules['nip']);
        $this->assertStringContainsString('email', $rules['email']);
        $this->assertStringContainsString('unique', $rules['email']);
    }

    public function test_update_teacher_authorization()
    {
        $request = new UpdateTeacher();
        $this->assertTrue($request->authorize());
    }

    public function test_update_teacher_validation_rules()
    {
        $request = new UpdateTeacher();
        $rules = $request->rules();

        $this->assertArrayHasKey('name', $rules);
        $this->assertArrayHasKey('nip', $rules);
        $this->assertArrayHasKey('email', $rules);
        $this->assertArrayHasKey('status', $rules);

        $this->assertStringContainsString('sometimes', $rules['name']);
        $this->assertStringContainsString('sometimes', $rules['nip']);
        $this->assertStringContainsString('unique', $rules['nip']);
    }

    public function test_form_request_error_messages()
    {
        $requests = [
            new StoreLeaveRequest(),
            new UpdateLeaveRequest(),
            new ApproveLeaveRequest(),
            new RejectLeaveRequest(),
            new StoreStudent(),
            new UpdateStudent(),
            new StoreTeacher(),
            new UpdateTeacher(),
        ];

        foreach ($requests as $request) {
            $messages = $request->messages();
            $this->assertIsArray($messages);
            $this->assertNotEmpty($messages);
        }
    }
}
