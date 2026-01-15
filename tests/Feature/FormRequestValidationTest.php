<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Http\Requests\Attendance\ApproveLeaveRequest;
use App\Http\Requests\Attendance\RejectLeaveRequest;
use App\Http\Requests\Attendance\StoreLeaveRequest;
use App\Http\Requests\Attendance\UpdateLeaveRequest;
use App\Http\Requests\SchoolManagement\StoreStudent;
use App\Http\Requests\SchoolManagement\StoreTeacher;
use App\Http\Requests\SchoolManagement\UpdateStudent;
use App\Http\Requests\SchoolManagement\UpdateTeacher;
use Tests\TestCase;

class FormRequestValidationTest extends TestCase
{
    public function testStoreStudentAuthorizesRequest()
    {
        $request = new StoreStudent();
        $this->assertTrue($request->authorize());
    }

    public function testStoreStudentValidatesRequiredFields()
    {
        $request = new StoreStudent();
        $rules = $request->rules();

        $this->assertArrayHasKey('name', $rules);
        $this->assertArrayHasKey('nisn', $rules);
        $this->assertArrayHasKey('class_id', $rules);
        $this->assertArrayHasKey('enrollment_year', $rules);
        $this->assertArrayHasKey('status', $rules);

        $this->assertStringContainsString('required', $rules['name']);
        $this->assertStringContainsString('required', $rules['nisn']);
        $this->assertStringContainsString('required', $rules['class_id']);
        $this->assertStringContainsString('required', $rules['enrollment_year']);
        $this->assertStringContainsString('required', $rules['status']);
    }

    public function testStoreStudentValidatesEmailFormat()
    {
        $request = new StoreStudent();
        $rules = $request->rules();

        $this->assertArrayHasKey('email', $rules);
        $this->assertStringContainsString('email', $rules['email']);
    }

    public function testStoreStudentValidatesUniqueNisn()
    {
        $request = new StoreStudent();
        $rules = $request->rules();

        $this->assertArrayHasKey('nisn', $rules);
        $this->assertStringContainsString('unique', $rules['nisn']);
    }

    public function testStoreStudentValidatesMaxLength()
    {
        $request = new StoreStudent();
        $rules = $request->rules();

        $this->assertStringContainsString('max:255', $rules['name']);
        $this->assertStringContainsString('max:50', $rules['nisn']);
        $this->assertStringContainsString('max:255', $rules['email']);
    }

    public function testStoreStudentValidatesExistsClass()
    {
        $request = new StoreStudent();
        $rules = $request->rules();

        $this->assertStringContainsString('exists:classes', $rules['class_id']);
    }

    public function testStoreStudentValidatesEnrollmentYearRange()
    {
        $request = new StoreStudent();
        $rules = $request->rules();

        $this->assertStringContainsString('min:1900', $rules['enrollment_year']);
        $this->assertStringContainsString('max:2100', $rules['enrollment_year']);
    }

    public function testStoreStudentValidatesStatusEnum()
    {
        $request = new StoreStudent();
        $rules = $request->rules();

        $this->assertStringContainsString('in:active,inactive,graduated', $rules['status']);
    }

    public function testUpdateStudentAuthorizesRequest()
    {
        $request = new UpdateStudent();
        $this->assertTrue($request->authorize());
    }

    public function testUpdateStudentValidatesOptionalFields()
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
    }

    public function testUpdateStudentHasCustomMessages()
    {
        $request = new UpdateStudent();
        $messages = $request->messages();

        $this->assertIsArray($messages);
        $this->assertArrayHasKey('name.string', $messages);
        $this->assertArrayHasKey('nisn.unique', $messages);
        $this->assertArrayHasKey('email.email', $messages);
    }

    public function testStoreTeacherAuthorizesRequest()
    {
        $request = new StoreTeacher();
        $this->assertTrue($request->authorize());
    }

    public function testStoreTeacherValidatesRequiredFields()
    {
        $request = new StoreTeacher();
        $rules = $request->rules();

        $this->assertArrayHasKey('name', $rules);
        $this->assertArrayHasKey('nip', $rules);
        $this->assertArrayHasKey('subject_id', $rules);
        $this->assertArrayHasKey('join_date', $rules);

        $this->assertStringContainsString('required', $rules['name']);
        $this->assertStringContainsString('required', $rules['nip']);
        $this->assertStringContainsString('required', $rules['subject_id']);
        $this->assertStringContainsString('required', $rules['join_date']);
    }

    public function testStoreTeacherValidatesEmailFormat()
    {
        $request = new StoreTeacher();
        $rules = $request->rules();

        $this->assertArrayHasKey('email', $rules);
        $this->assertStringContainsString('email', $rules['email']);
    }

    public function testStoreTeacherValidatesUniqueNip()
    {
        $request = new StoreTeacher();
        $rules = $request->rules();

        $this->assertArrayHasKey('nip', $rules);
        $this->assertStringContainsString('unique', $rules['nip']);
    }

    public function testStoreTeacherValidatesDateField()
    {
        $request = new StoreTeacher();
        $rules = $request->rules();

        $this->assertArrayHasKey('join_date', $rules);
        $this->assertStringContainsString('date', $rules['join_date']);
    }

    public function testUpdateTeacherAuthorizesRequest()
    {
        $request = new UpdateTeacher();
        $this->assertTrue($request->authorize());
    }

    public function testUpdateTeacherValidatesOptionalFields()
    {
        $request = new UpdateTeacher();
        $rules = $request->rules();

        $this->assertArrayHasKey('name', $rules);
        $this->assertArrayHasKey('nip', $rules);
        $this->assertArrayHasKey('email', $rules);

        $this->assertStringContainsString('sometimes', $rules['name']);
        $this->assertStringContainsString('sometimes', $rules['nip']);
    }

    public function testUpdateTeacherHasCustomMessages()
    {
        $request = new UpdateTeacher();
        $messages = $request->messages();

        $this->assertIsArray($messages);
        $this->assertArrayHasKey('name.string', $messages);
        $this->assertArrayHasKey('nip.unique', $messages);
        $this->assertArrayHasKey('email.email', $messages);
    }

    public function testStoreLeaveRequestAuthorizesRequest()
    {
        $request = new StoreLeaveRequest();
        $this->assertTrue($request->authorize());
    }

    public function testStoreLeaveRequestValidatesRequiredFields()
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

    public function testStoreLeaveRequestValidatesDateConstraints()
    {
        $request = new StoreLeaveRequest();
        $rules = $request->rules();

        $this->assertStringContainsString('after_or_equal:today', $rules['start_date']);
        $this->assertStringContainsString('after:start_date', $rules['end_date']);
    }

    public function testStoreLeaveRequestValidatesMaxLengths()
    {
        $request = new StoreLeaveRequest();
        $rules = $request->rules();

        $this->assertStringContainsString('max:500', $rules['reason']);
    }

    public function testUpdateLeaveRequestAuthorizesRequest()
    {
        $request = new UpdateLeaveRequest();
        $this->assertTrue($request->authorize());
    }

    public function testUpdateLeaveRequestValidatesComments()
    {
        $request = new UpdateLeaveRequest();
        $rules = $request->rules();

        $this->assertArrayHasKey('comments', $rules);
        $this->assertStringContainsString('sometimes', $rules['comments']);
        $this->assertStringContainsString('max:1000', $rules['comments']);
    }

    public function testApproveLeaveRequestAuthorizesRequest()
    {
        $request = new ApproveLeaveRequest();
        $this->assertTrue($request->authorize());
    }

    public function testApproveLeaveRequestValidatesOptionalComments()
    {
        $request = new ApproveLeaveRequest();
        $rules = $request->rules();

        $this->assertArrayHasKey('approval_comments', $rules);
        $this->assertStringContainsString('nullable', $rules['approval_comments']);
        $this->assertStringContainsString('max:500', $rules['approval_comments']);
    }

    public function testApproveLeaveRequestHasCustomMessages()
    {
        $request = new ApproveLeaveRequest();
        $messages = $request->messages();

        $this->assertArrayHasKey('approval_comments.string', $messages);
        $this->assertArrayHasKey('approval_comments.max', $messages);
    }

    public function testRejectLeaveRequestAuthorizesRequest()
    {
        $request = new RejectLeaveRequest();
        $this->assertTrue($request->authorize());
    }

    public function testRejectLeaveRequestValidatesRequiredComments()
    {
        $request = new RejectLeaveRequest();
        $rules = $request->rules();

        $this->assertArrayHasKey('approval_comments', $rules);
        $this->assertStringContainsString('required', $rules['approval_comments']);
        $this->assertStringContainsString('max:500', $rules['approval_comments']);
    }

    public function testRejectLeaveRequestHasCustomMessages()
    {
        $request = new RejectLeaveRequest();
        $messages = $request->messages();

        $this->assertArrayHasKey('approval_comments.required', $messages);
        $this->assertArrayHasKey('approval_comments.max', $messages);
    }
}
