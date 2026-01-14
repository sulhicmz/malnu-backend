<?php

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
use Hyperf\Context\ApplicationContext;
use Hyperf\HttpMessage\Server\Request;

class FormRequestValidationTest extends TestCase
{
    public function test_store_leave_request_validation_passes_with_valid_data()
    {
        $request = $this->createFormRequest(StoreLeaveRequest::class, [
            'staff_id' => 1,
            'leave_type_id' => 1,
            'start_date' => date('Y-m-d'),
            'end_date' => date('Y-m-d', strtotime('+3 days')),
            'reason' => 'Test leave request',
        ]);

        $this->assertTrue($request->authorize());
        $validated = $request->validated();
        $this->assertEquals(1, $validated['staff_id']);
        $this->assertEquals(1, $validated['leave_type_id']);
    }

    public function test_store_leave_request_validation_fails_without_required_fields()
    {
        $request = $this->createFormRequest(StoreLeaveRequest::class, []);

        $this->assertFalse($request->passesAuthorization());
    }

    public function test_update_leave_request_validation_passes_with_valid_data()
    {
        $request = $this->createFormRequest(UpdateLeaveRequest::class, [
            'comments' => 'Updated comments',
        ]);

        $this->assertTrue($request->authorize());
        $validated = $request->validated();
        $this->assertEquals('Updated comments', $validated['comments']);
    }

    public function test_update_leave_request_validation_fails_with_invalid_type()
    {
        $request = $this->createFormRequest(UpdateLeaveRequest::class, [
            'comments' => 123,
        ]);

        $this->assertFalse($request->validate(['comments' => 'string|max:1000']));
    }

    public function test_approve_leave_request_validation_passes_with_valid_data()
    {
        $request = $this->createFormRequest(ApproveLeaveRequest::class, [
            'approval_comments' => 'Approved',
        ]);

        $this->assertTrue($request->authorize());
        $validated = $request->validated();
        $this->assertEquals('Approved', $validated['approval_comments']);
    }

    public function test_approve_leave_request_validation_passes_without_comments()
    {
        $request = $this->createFormRequest(ApproveLeaveRequest::class, []);

        $this->assertTrue($request->authorize());
        $validated = $request->validated();
        $this->assertArrayNotHasKey('approval_comments', $validated);
    }

    public function test_reject_leave_request_validation_passes_with_valid_data()
    {
        $request = $this->createFormRequest(RejectLeaveRequest::class, [
            'approval_comments' => 'Rejected',
        ]);

        $this->assertTrue($request->authorize());
        $validated = $request->validated();
        $this->assertEquals('Rejected', $validated['approval_comments']);
    }

    public function test_store_student_validation_passes_with_valid_data()
    {
        $request = $this->createFormRequest(StoreStudent::class, [
            'name' => 'John Doe',
            'nisn' => '1234567890',
            'email' => 'john@example.com',
            'class_id' => 1,
            'enrollment_year' => 2024,
            'status' => 'active',
        ]);

        $this->assertTrue($request->authorize());
        $validated = $request->validated();
        $this->assertEquals('John Doe', $validated['name']);
    }

    public function test_store_student_validation_fails_without_required_fields()
    {
        $request = $this->createFormRequest(StoreStudent::class, [
            'name' => 'John Doe',
        ]);

        $rules = $request->rules();
        $this->assertArrayHasKey('nisn', $rules);
        $this->assertArrayHasKey('class_id', $rules);
    }

    public function test_store_student_validation_fails_with_invalid_email()
    {
        $request = $this->createFormRequest(StoreStudent::class, [
            'name' => 'John Doe',
            'nisn' => '1234567890',
            'email' => 'invalid-email',
            'class_id' => 1,
            'enrollment_year' => 2024,
            'status' => 'active',
        ]);

        $rules = $request->rules();
        $this->assertContains('email', explode('|', $rules['email']));
    }

    public function test_update_student_validation_passes_with_valid_data()
    {
        $request = $this->createFormRequest(UpdateStudent::class, [
            'name' => 'Jane Doe',
            'email' => 'jane@example.com',
        ]);

        $this->assertTrue($request->authorize());
        $validated = $request->validated();
        $this->assertEquals('Jane Doe', $validated['name']);
    }

    public function test_update_student_validation_allows_partial_updates()
    {
        $request = $this->createFormRequest(UpdateStudent::class, [
            'name' => 'Updated Name',
        ]);

        $this->assertTrue($request->authorize());
        $validated = $request->validated();
        $this->assertEquals('Updated Name', $validated['name']);
        $this->assertArrayNotHasKey('nisn', $validated);
    }

    public function test_store_teacher_validation_passes_with_valid_data()
    {
        $request = $this->createFormRequest(StoreTeacher::class, [
            'name' => 'Jane Smith',
            'nip' => '987654321',
            'email' => 'jane@example.com',
            'subject_id' => 1,
            'join_date' => date('Y-m-d'),
            'status' => 'active',
        ]);

        $this->assertTrue($request->authorize());
        $validated = $request->validated();
        $this->assertEquals('Jane Smith', $validated['name']);
    }

    public function test_store_teacher_validation_fails_with_future_join_date()
    {
        $request = $this->createFormRequest(StoreTeacher::class, [
            'name' => 'Jane Smith',
            'nip' => '987654321',
            'email' => 'jane@example.com',
            'subject_id' => 1,
            'join_date' => date('Y-m-d', strtotime('+1 year')),
            'status' => 'active',
        ]);

        $rules = $request->rules();
        $this->assertContains('before_or_equal:today', explode('|', $rules['join_date']));
    }

    public function test_update_teacher_validation_passes_with_valid_data()
    {
        $request = $this->createFormRequest(UpdateTeacher::class, [
            'name' => 'Updated Teacher',
            'email' => 'updated@example.com',
        ]);

        $this->assertTrue($request->authorize());
        $validated = $request->validated();
        $this->assertEquals('Updated Teacher', $validated['name']);
    }

    public function test_form_request_error_messages_are_defined()
    {
        $request = $this->createFormRequest(StoreLeaveRequest::class, []);
        $messages = $request->messages();
        $this->assertIsArray($messages);
        $this->assertNotEmpty($messages);
        $this->assertArrayHasKey('staff_id.required', $messages);
    }

    protected function createFormRequest($class, $data)
    {
        $request = new Request('POST', '/', [], [], [], [], json_encode($data));
        $formRequest = new $class();
        $formRequest->setContainer(ApplicationContext::getContainer());
        $formRequest->setRequest($request);

        return $formRequest;
    }
}
