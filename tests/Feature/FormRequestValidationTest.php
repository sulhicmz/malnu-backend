<?php

declare(strict_types=1);

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

class FormRequestValidationTest extends TestCase
{
    public function testStoreLeaveRequestValidationPasses()
    {
        $request = new StoreLeaveRequest();
        $request->replace([
            'staff_id' => 1,
            'leave_type_id' => 1,
            'start_date' => date('Y-m-d'),
            'end_date' => date('Y-m-d', strtotime('+2 days')),
            'reason' => 'Family emergency',
        ]);

        $this->assertTrue($request->authorize());
        $rules = $request->rules();
        $this->assertIsArray($rules);
        $this->assertArrayHasKey('staff_id', $rules);
        $this->assertArrayHasKey('leave_type_id', $rules);
        $this->assertArrayHasKey('start_date', $rules);
        $this->assertArrayHasKey('end_date', $rules);
        $this->assertArrayHasKey('reason', $rules);
    }

    public function testUpdateLeaveRequestValidationPasses()
    {
        $request = new UpdateLeaveRequest();
        $request->replace([
            'comments' => 'Updating my leave request details',
        ]);

        $this->assertTrue($request->authorize());
        $rules = $request->rules();
        $this->assertIsArray($rules);
        $this->assertArrayHasKey('comments', $rules);
    }

    public function testApproveLeaveRequestValidationPasses()
    {
        $request = new ApproveLeaveRequest();
        $request->replace([
            'approval_comments' => 'Approved for personal reasons',
        ]);

        $this->assertTrue($request->authorize());
        $rules = $request->rules();
        $this->assertIsArray($rules);
        $this->assertArrayHasKey('approval_comments', $rules);
    }

    public function testApproveLeaveRequestAllowsNullableComments()
    {
        $request = new ApproveLeaveRequest();
        $request->replace([]);

        $this->assertTrue($request->authorize());
        $rules = $request->rules();
        $this->assertStringContainsString('nullable', $rules['approval_comments']);
    }

    public function testRejectLeaveRequestValidationPasses()
    {
        $request = new RejectLeaveRequest();
        $request->replace([
            'approval_comments' => 'Rejecting due to insufficient staff coverage',
        ]);

        $this->assertTrue($request->authorize());
        $rules = $request->rules();
        $this->assertIsArray($rules);
        $this->assertArrayHasKey('approval_comments', $rules);
    }

    public function testRejectLeaveRequestRequiresComments()
    {
        $request = new RejectLeaveRequest();
        $request->replace([]);

        $this->assertTrue($request->authorize());
        $rules = $request->rules();
        $this->assertStringContainsString('required', $rules['approval_comments']);
    }

    public function testStoreStudentValidationPasses()
    {
        $request = new StoreStudent();
        $request->replace([
            'name' => 'John Doe',
            'nisn' => '1234567890',
            'email' => 'john.doe@example.com',
            'class_id' => 1,
            'enrollment_year' => 2025,
            'status' => 'active',
        ]);

        $this->assertTrue($request->authorize());
        $rules = $request->rules();
        $this->assertIsArray($rules);
        $this->assertArrayHasKey('name', $rules);
        $this->assertArrayHasKey('nisn', $rules);
        $this->assertArrayHasKey('email', $rules);
        $this->assertArrayHasKey('class_id', $rules);
        $this->assertArrayHasKey('enrollment_year', $rules);
        $this->assertArrayHasKey('status', $rules);
    }

    public function testUpdateStudentValidationPasses()
    {
        $request = new UpdateStudent();
        $request->merge([
            'id' => 1,
        ]);
        $request->replace([
            'name' => 'Jane Doe',
        ]);

        $this->assertTrue($request->authorize());
        $rules = $request->rules();
        $this->assertIsArray($rules);
        $this->assertArrayHasKey('name', $rules);
        $this->assertStringContainsString('sometimes', $rules['name']);
    }

    public function testStoreTeacherValidationPasses()
    {
        $request = new StoreTeacher();
        $request->replace([
            'name' => 'Mr. Smith',
            'nip' => '19800101',
            'email' => 'smith@example.com',
            'subject_id' => 1,
            'join_date' => date('Y-m-d'),
            'status' => 'active',
        ]);

        $this->assertTrue($request->authorize());
        $rules = $request->rules();
        $this->assertIsArray($rules);
        $this->assertArrayHasKey('name', $rules);
        $this->assertArrayHasKey('nip', $rules);
        $this->assertArrayHasKey('email', $rules);
        $this->assertArrayHasKey('subject_id', $rules);
        $this->assertArrayHasKey('join_date', $rules);
        $this->assertArrayHasKey('status', $rules);
    }

    public function testUpdateTeacherValidationPasses()
    {
        $request = new UpdateTeacher();
        $request->merge([
            'id' => 1,
        ]);
        $request->replace([
            'name' => 'Ms. Johnson',
        ]);

        $this->assertTrue($request->authorize());
        $rules = $request->rules();
        $this->assertIsArray($rules);
        $this->assertArrayHasKey('name', $rules);
        $this->assertStringContainsString('sometimes', $rules['name']);
    }

    public function testEmailValidationRule()
    {
        $studentRequest = new StoreStudent();
        $rules = $studentRequest->rules();
        $this->assertStringContainsString('email', $rules['email']);

        $teacherRequest = new StoreTeacher();
        $rules = $teacherRequest->rules();
        $this->assertStringContainsString('email', $rules['email']);
    }

    public function testUniqueFieldValidation()
    {
        $studentRequest = new StoreStudent();
        $rules = $studentRequest->rules();
        $this->assertStringContainsString('unique', $rules['nisn']);
        $this->assertStringContainsString('unique', $rules['email']);

        $teacherRequest = new StoreTeacher();
        $rules = $teacherRequest->rules();
        $this->assertStringContainsString('unique', $rules['nip']);
        $this->assertStringContainsString('unique', $rules['email']);
    }

    public function testExistsValidationRule()
    {
        $leaveRequest = new StoreLeaveRequest();
        $rules = $leaveRequest->rules();
        $this->assertStringContainsString('exists', $rules['staff_id']);
        $this->assertStringContainsString('exists', $rules['leave_type_id']);
    }

    public function testDateValidationRules()
    {
        $leaveRequest = new StoreLeaveRequest();
        $rules = $leaveRequest->rules();
        $this->assertStringContainsString('date', $rules['start_date']);
        $this->assertStringContainsString('date', $rules['end_date']);

        $teacherRequest = new StoreTeacher();
        $rules = $teacherRequest->rules();
        $this->assertStringContainsString('date', $rules['join_date']);
    }

    public function testStringLengthValidation()
    {
        $leaveRequest = new StoreLeaveRequest();
        $rules = $leaveRequest->rules();
        $this->assertStringContainsString('max:500', $rules['reason']);

        $studentRequest = new StoreStudent();
        $rules = $studentRequest->rules();
        $this->assertStringContainsString('max:255', $rules['name']);
        $this->assertStringContainsString('max:50', $rules['nisn']);
    }

    public function testStatusValidationRules()
    {
        $studentRequest = new StoreStudent();
        $rules = $studentRequest->rules();
        $this->assertStringContainsString('in:active,inactive,graduated', $rules['status']);

        $teacherRequest = new StoreTeacher();
        $rules = $teacherRequest->rules();
        $this->assertStringContainsString('in:active,inactive', $rules['status']);
    }

    public function testAllFormRequestsAuthorizeReturnsTrue()
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
            $this->assertTrue($request->authorize(), get_class($request) . ' should authorize');
        }
    }

    public function testAllFormRequestsHaveRules()
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
            $rules = $request->rules();
            $this->assertIsArray($rules, get_class($request) . ' should have rules array');
            $this->assertNotEmpty($rules, get_class($request) . ' rules should not be empty');
        }
    }

    public function testAllFormRequestsHaveMessages()
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
            $this->assertIsArray($messages, get_class($request) . ' should have messages array');
        }
    }
}
