<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Http\Requests\Attendance\StoreLeaveRequest;
use App\Http\Requests\Attendance\UpdateLeaveRequest;
use App\Http\Requests\Attendance\ApproveLeaveRequest;
use App\Http\Requests\Attendance\RejectLeaveRequest;
use App\Http\Requests\SchoolManagement\StoreStudent;
use App\Http\Requests\SchoolManagement\UpdateStudent;
use App\Http\Requests\SchoolManagement\StoreTeacher;
use App\Http\Requests\SchoolManagement\UpdateTeacher;
use Tests\TestCase;

class FormRequestValidationTest extends TestCase
{
    public function testStoreLeaveRequestAuthorization()
    {
        $request = new StoreLeaveRequest();
        $this->assertTrue($request->authorize());
    }

    public function testStoreLeaveRequestValidationRules()
    {
        $request = new StoreLeaveRequest();
        $rules = $request->rules();

        $this->assertArrayHasKey('staff_id', $rules);
        $this->assertArrayHasKey('leave_type_id', $rules);
        $this->assertArrayHasKey('start_date', $rules);
        $this->assertArrayHasKey('end_date', $rules);
        $this->assertArrayHasKey('reason', $rules);

        $this->assertStringContainsString('required', $rules['staff_id']);
        $this->assertStringContainsString('required', $rules['leave_type_id']);
        $this->assertStringContainsString('required', $rules['start_date']);
        $this->assertStringContainsString('required', $rules['end_date']);
        $this->assertStringContainsString('required', $rules['reason']);
    }

    public function testStoreLeaveRequestValidationMessages()
    {
        $request = new StoreLeaveRequest();
        $messages = $request->messages();

        $this->assertArrayHasKey('staff_id.required', $messages);
        $this->assertArrayHasKey('staff_id.exists', $messages);
        $this->assertArrayHasKey('leave_type_id.required', $messages);
        $this->assertArrayHasKey('leave_type_id.exists', $messages);
        $this->assertArrayHasKey('start_date.required', $messages);
        $this->assertArrayHasKey('start_date.after_or_equal', $messages);
        $this->assertArrayHasKey('end_date.after', $messages);
        $this->assertArrayHasKey('reason.required', $messages);
    }

    public function testUpdateLeaveRequestAuthorization()
    {
        $request = new UpdateLeaveRequest();
        $this->assertTrue($request->authorize());
    }

    public function testUpdateLeaveRequestValidationRules()
    {
        $request = new UpdateLeaveRequest();
        $rules = $request->rules();

        $this->assertArrayHasKey('comments', $rules);
        $this->assertStringContainsString('sometimes', $rules['comments']);
        $this->assertStringContainsString('string', $rules['comments']);
        $this->assertStringContainsString('max:1000', $rules['comments']);
    }

    public function testApproveLeaveRequestAuthorization()
    {
        $request = new ApproveLeaveRequest();
        $this->assertTrue($request->authorize());
    }

    public function testApproveLeaveRequestValidationRules()
    {
        $request = new ApproveLeaveRequest();
        $rules = $request->rules();

        $this->assertArrayHasKey('approval_comments', $rules);
        $this->assertStringContainsString('nullable', $rules['approval_comments']);
        $this->assertStringContainsString('string', $rules['approval_comments']);
        $this->assertStringContainsString('max:1000', $rules['approval_comments']);
    }

    public function testRejectLeaveRequestAuthorization()
    {
        $request = new RejectLeaveRequest();
        $this->assertTrue($request->authorize());
    }

    public function testRejectLeaveRequestValidationRules()
    {
        $request = new RejectLeaveRequest();
        $rules = $request->rules();

        $this->assertArrayHasKey('approval_comments', $rules);
        $this->assertStringContainsString('required', $rules['approval_comments']);
        $this->assertStringContainsString('string', $rules['approval_comments']);
        $this->assertStringContainsString('max:1000', $rules['approval_comments']);
    }

    public function testStoreStudentAuthorization()
    {
        $request = new StoreStudent();
        $this->assertTrue($request->authorize());
    }

    public function testStoreStudentValidationRules()
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
        $this->assertStringContainsString('required', $rules['class_id']);
        $this->assertStringContainsString('required', $rules['enrollment_year']);
        $this->assertStringContainsString('required', $rules['status']);
        $this->assertStringContainsString('unique:students,nisn', $rules['nisn']);
        $this->assertStringContainsString('unique:students,email', $rules['email']);
        $this->assertStringContainsString('in:active,inactive,graduated', $rules['status']);
    }

    public function testUpdateStudentAuthorization()
    {
        $request = new UpdateStudent();
        $this->assertTrue($request->authorize());
    }

    public function testUpdateStudentValidationRules()
    {
        $request = new UpdateStudent();
        $rules = $request->rules();

        $this->assertArrayHasKey('name', $rules);
        $this->assertArrayHasKey('nisn', $rules);
        $this->assertArrayHasKey('email', $rules);
        $this->assertArrayHasKey('status', $rules);

        $this->assertStringContainsString('sometimes', $rules['name']);
        $this->assertStringContainsString('sometimes', $rules['nisn']);
        $this->assertStringContainsString('sometimes', $rules['status']);
        $this->assertStringContainsString('in:active,inactive,graduated', $rules['status']);
    }

    public function testStoreTeacherAuthorization()
    {
        $request = new StoreTeacher();
        $this->assertTrue($request->authorize());
    }

    public function testStoreTeacherValidationRules()
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
        $this->assertStringContainsString('required', $rules['subject_id']);
        $this->assertStringContainsString('required', $rules['join_date']);
        $this->assertStringContainsString('required', $rules['status']);
        $this->assertStringContainsString('unique:teachers,nip', $rules['nip']);
        $this->assertStringContainsString('unique:teachers,email', $rules['email']);
        $this->assertStringContainsString('in:active,inactive', $rules['status']);
    }

    public function testUpdateTeacherAuthorization()
    {
        $request = new UpdateTeacher();
        $this->assertTrue($request->authorize());
    }

    public function testUpdateTeacherValidationRules()
    {
        $request = new UpdateTeacher();
        $rules = $request->rules();

        $this->assertArrayHasKey('name', $rules);
        $this->assertArrayHasKey('nip', $rules);
        $this->assertArrayHasKey('email', $rules);
        $this->assertArrayHasKey('status', $rules);

        $this->assertStringContainsString('sometimes', $rules['name']);
        $this->assertStringContainsString('sometimes', $rules['nip']);
        $this->assertStringContainsString('sometimes', $rules['status']);
        $this->assertStringContainsString('in:active,inactive', $rules['status']);
    }

    public function testAllFormRequestsHaveAuthorization()
    {
        $requests = [
            StoreLeaveRequest::class,
            UpdateLeaveRequest::class,
            ApproveLeaveRequest::class,
            RejectLeaveRequest::class,
            StoreStudent::class,
            UpdateStudent::class,
            StoreTeacher::class,
            UpdateTeacher::class,
        ];

        foreach ($requests as $requestClass) {
            $request = new $requestClass();
            $this->assertTrue($request->authorize(), "Authorization for {$requestClass} should return true");
        }
    }

    public function testAllFormRequestsHaveRulesMethod()
    {
        $requests = [
            StoreLeaveRequest::class,
            UpdateLeaveRequest::class,
            ApproveLeaveRequest::class,
            RejectLeaveRequest::class,
            StoreStudent::class,
            UpdateStudent::class,
            StoreTeacher::class,
            UpdateTeacher::class,
        ];

        foreach ($requests as $requestClass) {
            $request = new $requestClass();
            $rules = $request->rules();
            $this->assertIsArray($rules, "Rules for {$requestClass} should return an array");
            $this->assertNotEmpty($rules, "Rules for {$requestClass} should not be empty");
        }
    }

    public function testAllFormRequestsHaveMessagesMethod()
    {
        $requests = [
            StoreLeaveRequest::class,
            UpdateLeaveRequest::class,
            ApproveLeaveRequest::class,
            RejectLeaveRequest::class,
            StoreStudent::class,
            UpdateStudent::class,
            StoreTeacher::class,
            UpdateTeacher::class,
        ];

        foreach ($requests as $requestClass) {
            $request = new $requestClass();
            $messages = $request->messages();
            $this->assertIsArray($messages, "Messages for {$requestClass} should return an array");
        }
    }
}
