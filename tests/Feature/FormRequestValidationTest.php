<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Http\Requests\Attendance\StoreLeaveRequest;
use App\Http\Requests\Attendance\UpdateLeaveRequest;
use App\Http\Requests\SchoolManagement\StoreStudent;
use App\Http\Requests\SchoolManagement\UpdateStudent;
use App\Http\Requests\SchoolManagement\StoreTeacher;
use App\Http\Requests\SchoolManagement\UpdateTeacher;
use Tests\TestCase;

/**
 * @internal
 * @coversNothing
 */
class FormRequestValidationTest extends TestCase
{
    /**
     * Test StoreLeaveRequest validation rules.
     */
    public function testStoreLeaveRequestValidation(): void
    {
        $request = new StoreLeaveRequest();

        $rules = $request->rules();

        $this->assertIsArray($rules);
        $this->assertArrayHasKey('staff_id', $rules);
        $this->assertArrayHasKey('leave_type_id', $rules);
        $this->assertArrayHasKey('start_date', $rules);
        $this->assertArrayHasKey('end_date', $rules);
        $this->assertArrayHasKey('reason', $rules);

        $this->assertStringContainsString('required', $rules['staff_id']);
        $this->assertStringContainsString('integer', $rules['staff_id']);
        $this->assertStringContainsString('exists', $rules['staff_id']);

        $this->assertStringContainsString('required', $rules['leave_type_id']);
        $this->assertStringContainsString('after_or_equal', $rules['start_date']);
        $this->assertStringContainsString('after', $rules['end_date']);
        $this->assertStringContainsString('max', $rules['reason']);
    }

    /**
     * Test StoreLeaveRequest custom messages.
     */
    public function testStoreLeaveRequestMessages(): void
    {
        $request = new StoreLeaveRequest();

        $messages = $request->messages();

        $this->assertIsArray($messages);
        $this->assertArrayHasKey('staff_id.required', $messages);
        $this->assertArrayHasKey('leave_type_id.required', $messages);
        $this->assertArrayHasKey('start_date.date', $messages);
        $this->assertArrayHasKey('end_date.after', $messages);
        $this->assertArrayHasKey('reason.max', $messages);
    }

    /**
     * Test UpdateLeaveRequest validation rules.
     */
    public function testUpdateLeaveRequestValidation(): void
    {
        $request = new UpdateLeaveRequest();

        $rules = $request->rules();

        $this->assertIsArray($rules);
        $this->assertArrayHasKey('comments', $rules);

        $this->assertStringContainsString('sometimes', $rules['comments']);
        $this->assertStringContainsString('string', $rules['comments']);
        $this->assertStringContainsString('max', $rules['comments']);
    }

    /**
     * Test UpdateLeaveRequest custom messages.
     */
    public function testUpdateLeaveRequestMessages(): void
    {
        $request = new UpdateLeaveRequest();

        $messages = $request->messages();

        $this->assertIsArray($messages);
        $this->assertArrayHasKey('comments.string', $messages);
        $this->assertArrayHasKey('comments.max', $messages);
    }

    /**
     * Test StoreStudent validation rules.
     */
    public function testStoreStudentValidation(): void
    {
        $request = new StoreStudent();

        $rules = $request->rules();

        $this->assertIsArray($rules);
        $this->assertArrayHasKey('name', $rules);
        $this->assertArrayHasKey('nisn', $rules);
        $this->assertArrayHasKey('email', $rules);
        $this->assertArrayHasKey('class_id', $rules);
        $this->assertArrayHasKey('enrollment_year', $rules);
        $this->assertArrayHasKey('status', $rules);

        $this->assertStringContainsString('required', $rules['name']);
        $this->assertStringContainsString('max', $rules['name']);

        $this->assertStringContainsString('required', $rules['nisn']);
        $this->assertStringContainsString('unique', $rules['nisn']);

        $this->assertStringContainsString('email', $rules['email']);
        $this->assertStringContainsString('unique', $rules['email']);

        $this->assertStringContainsString('required', $rules['class_id']);
        $this->assertStringContainsString('exists', $rules['class_id']);

        $this->assertStringContainsString('required', $rules['enrollment_year']);
        $this->assertStringContainsString('integer', $rules['enrollment_year']);

        $this->assertStringContainsString('required', $rules['status']);
        $this->assertStringContainsString('in', $rules['status']);
    }

    /**
     * Test StoreStudent custom messages.
     */
    public function testStoreStudentMessages(): void
    {
        $request = new StoreStudent();

        $messages = $request->messages();

        $this->assertIsArray($messages);
        $this->assertArrayHasKey('name.required', $messages);
        $this->assertArrayHasKey('nisn.unique', $messages);
        $this->assertArrayHasKey('email.email', $messages);
        $this->assertArrayHasKey('email.unique', $messages);
        $this->assertArrayHasKey('enrollment_year.min', $messages);
        $this->assertArrayHasKey('enrollment_year.max', $messages);
        $this->assertArrayHasKey('status.in', $messages);
    }

    /**
     * Test UpdateStudent validation rules.
     */
    public function testUpdateStudentValidation(): void
    {
        $request = new UpdateStudent();

        $rules = $request->rules();

        $this->assertIsArray($rules);
        $this->assertArrayHasKey('name', $rules);
        $this->assertArrayHasKey('nisn', $rules);
        $this->assertArrayHasKey('email', $rules);
        $this->assertArrayHasKey('status', $rules);

        $this->assertStringContainsString('sometimes', $rules['name']);
        $this->assertStringContainsString('sometimes', $rules['nisn']);
        $this->assertStringContainsString('sometimes', $rules['status']);

        $this->assertStringContainsString('unique', $rules['nisn']);
        $this->assertStringContainsString('unique', $rules['email']);

        $this->assertStringContainsString('in', $rules['status']);
    }

    /**
     * Test UpdateStudent custom messages.
     */
    public function testUpdateStudentMessages(): void
    {
        $request = new UpdateStudent();

        $messages = $request->messages();

        $this->assertIsArray($messages);
        $this->assertArrayHasKey('name.string', $messages);
        $this->assertArrayHasKey('nisn.unique', $messages);
        $this->assertArrayHasKey('email.email', $messages);
        $this->assertArrayHasKey('email.unique', $messages);
        $this->assertArrayHasKey('status.in', $messages);
    }

    /**
     * Test StoreTeacher validation rules.
     */
    public function testStoreTeacherValidation(): void
    {
        $request = new StoreTeacher();

        $rules = $request->rules();

        $this->assertIsArray($rules);
        $this->assertArrayHasKey('name', $rules);
        $this->assertArrayHasKey('nip', $rules);
        $this->assertArrayHasKey('email', $rules);
        $this->assertArrayHasKey('subject_id', $rules);
        $this->assertArrayHasKey('join_date', $rules);
        $this->assertArrayHasKey('status', $rules);

        $this->assertStringContainsString('required', $rules['name']);
        $this->assertStringContainsString('max', $rules['name']);

        $this->assertStringContainsString('required', $rules['nip']);
        $this->assertStringContainsString('unique', $rules['nip']);

        $this->assertStringContainsString('email', $rules['email']);
        $this->assertStringContainsString('unique', $rules['email']);

        $this->assertStringContainsString('required', $rules['subject_id']);
        $this->assertStringContainsString('exists', $rules['subject_id']);

        $this->assertStringContainsString('required', $rules['join_date']);
        $this->assertStringContainsString('before_or_equal', $rules['join_date']);

        $this->assertStringContainsString('required', $rules['status']);
        $this->assertStringContainsString('in', $rules['status']);
    }

    /**
     * Test StoreTeacher custom messages.
     */
    public function testStoreTeacherMessages(): void
    {
        $request = new StoreTeacher();

        $messages = $request->messages();

        $this->assertIsArray($messages);
        $this->assertArrayHasKey('name.required', $messages);
        $this->assertArrayHasKey('nip.unique', $messages);
        $this->assertArrayHasKey('email.email', $messages);
        $this->assertArrayHasKey('email.unique', $messages);
        $this->assertArrayHasKey('join_date.before_or_equal', $messages);
        $this->assertArrayHasKey('status.in', $messages);
    }

    /**
     * Test UpdateTeacher validation rules.
     */
    public function testUpdateTeacherValidation(): void
    {
        $request = new UpdateTeacher();

        $rules = $request->rules();

        $this->assertIsArray($rules);
        $this->assertArrayHasKey('name', $rules);
        $this->assertArrayHasKey('nip', $rules);
        $this->assertArrayHasKey('email', $rules);
        $this->assertArrayHasKey('status', $rules);

        $this->assertStringContainsString('sometimes', $rules['name']);
        $this->assertStringContainsString('sometimes', $rules['nip']);
        $this->assertStringContainsString('sometimes', $rules['status']);

        $this->assertStringContainsString('unique', $rules['nip']);
        $this->assertStringContainsString('unique', $rules['email']);

        $this->assertStringContainsString('in', $rules['status']);
    }

    /**
     * Test UpdateTeacher custom messages.
     */
    public function testUpdateTeacherMessages(): void
    {
        $request = new UpdateTeacher();

        $messages = $request->messages();

        $this->assertIsArray($messages);
        $this->assertArrayHasKey('name.string', $messages);
        $this->assertArrayHasKey('nip.unique', $messages);
        $this->assertArrayHasKey('email.email', $messages);
        $this->assertArrayHasKey('email.unique', $messages);
        $this->assertArrayHasKey('status.in', $messages);
    }

    /**
     * Test all Form Request classes have authorize method.
     */
    public function testFormRequestsHaveAuthorizeMethod(): void
    {
        $requests = [
            StoreLeaveRequest::class,
            UpdateLeaveRequest::class,
            StoreStudent::class,
            UpdateStudent::class,
            StoreTeacher::class,
            UpdateTeacher::class,
        ];

        foreach ($requests as $requestClass) {
            $request = new $requestClass();
            $this->assertTrue($request->authorize(), "{$requestClass} should authorize requests");
        }
    }

    /**
     * Test all Form Request classes extend FormRequest.
     */
    public function testFormRequestsExtendFormRequest(): void
    {
        $requests = [
            StoreLeaveRequest::class,
            UpdateLeaveRequest::class,
            StoreStudent::class,
            UpdateStudent::class,
            StoreTeacher::class,
            UpdateTeacher::class,
        ];

        foreach ($requests as $requestClass) {
            $this->assertInstanceOf(\Hyperf\Foundation\Http\FormRequest::class, new $requestClass());
        }
    }
}
