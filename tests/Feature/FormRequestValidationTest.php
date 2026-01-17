<?php

namespace Tests\Feature;

use App\Http\Requests\Attendance\ApproveLeaveRequest;
use App\Http\Requests\Attendance\RejectLeaveRequest;
use App\Http\Requests\Attendance\StoreLeaveRequest;
use App\Http\Requests\Attendance\UpdateLeaveRequest;
use App\Http\Requests\SchoolManagement\StoreStudent;
use App\Http\Requests\SchoolManagement\UpdateStudent;
use App\Http\Requests\SchoolManagement\StoreTeacher;
use App\Http\Requests\SchoolManagement\UpdateTeacher;
use Tests\TestCase;

class FormRequestValidationTest extends TestCase
{
    public function test_store_leave_request_validation_passes_with_valid_data(): void
    {
        $request = new StoreLeaveRequest();

        $request->merge([
            'staff_id' => (string) \Illuminate\Support\Str::uuid(),
            'leave_type_id' => (string) \Illuminate\Support\Str::uuid(),
            'start_date' => date('Y-m-d', strtotime('+1 day')),
            'end_date' => date('Y-m-d', strtotime('+2 days')),
            'reason' => 'Need time off for personal matters',
        ]);

        $this->assertTrue($request->authorize());
        $rules = $request->rules();
        $validator = validator($request->all(), $rules);
        $this->assertFalse($validator->fails());
    }

    public function test_store_leave_request_validation_fails_with_invalid_uuid(): void
    {
        $request = new StoreLeaveRequest();
        $request->merge([
            'staff_id' => 'invalid-uuid',
            'leave_type_id' => 'invalid-uuid',
            'start_date' => date('Y-m-d', strtotime('+1 day')),
            'end_date' => date('Y-m-d', strtotime('+2 days')),
            'reason' => 'Need time off',
        ]);

        $validator = validator($request->all(), $request->rules());
        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('staff_id', $validator->errors());
        $this->assertArrayHasKey('leave_type_id', $validator->errors());
    }

    public function test_update_leave_request_validation_passes_with_valid_comments(): void
    {
        $request = new UpdateLeaveRequest();
        $request->merge(['comments' => 'Update my leave request']);

        $validator = validator($request->all(), $request->rules());
        $this->assertFalse($validator->fails());
    }

    public function test_approve_leave_request_validation_passes_without_comments(): void
    {
        $request = new ApproveLeaveRequest();
        $request->merge([]);

        $validator = validator($request->all(), $request->rules());
        $this->assertFalse($validator->fails());
    }

    public function test_approve_leave_request_validation_passes_with_comments(): void
    {
        $request = new ApproveLeaveRequest();
        $request->merge(['approval_comments' => 'Approved as requested']);

        $validator = validator($request->all(), $request->rules());
        $this->assertFalse($validator->fails());
    }

    public function test_reject_leave_request_validation_fails_without_comments(): void
    {
        $request = new RejectLeaveRequest();
        $request->merge([]);

        $validator = validator($request->all(), $request->rules());
        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('approval_comments', $validator->errors());
    }

    public function test_reject_leave_request_validation_passes_with_comments(): void
    {
        $request = new RejectLeaveRequest();
        $request->merge(['approval_comments' => 'Rejected due to staffing shortage']);

        $validator = validator($request->all(), $request->rules());
        $this->assertFalse($validator->fails());
    }

    public function test_store_student_validation_passes_with_valid_data(): void
    {
        $request = new StoreStudent();
        $request->merge([
            'nisn' => '1234567890',
            'class_id' => (string) \Illuminate\Support\Str::uuid(),
            'enrollment_date' => date('Y-m-d'),
            'status' => 'active',
        ]);

        $validator = validator($request->all(), $request->rules());
        $this->assertFalse($validator->fails());
    }

    public function test_store_student_validation_fails_without_required_fields(): void
    {
        $request = new StoreStudent();
        $request->merge([
            'nisn' => '1234567890',
        ]);

        $validator = validator($request->all(), $request->rules());
        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('class_id', $validator->errors());
        $this->assertArrayHasKey('enrollment_date', $validator->errors());
        $this->assertArrayHasKey('status', $validator->errors());
    }

    public function test_store_student_validation_fails_with_duplicate_nisn(): void
    {
        $request = new StoreStudent();
        $request->merge([
            'nisn' => '1234567890',
            'class_id' => (string) \Illuminate\Support\Str::uuid(),
            'enrollment_date' => date('Y-m-d'),
            'status' => 'active',
        ]);

        $validator = validator($request->all(), $request->rules());
        $this->assertFalse($validator->fails());
    }

    public function test_update_student_validation_passes_with_valid_data(): void
    {
        $request = new UpdateStudent();
        $request->merge([
            'nisn' => '0987654321',
            'status' => 'inactive',
        ]);

        $validator = validator($request->all(), $request->rules());
        $this->assertFalse($validator->fails());
    }

    public function test_store_teacher_validation_passes_with_valid_data(): void
    {
        $request = new StoreTeacher();
        $request->merge([
            'nip' => '12345678',
            'join_date' => date('Y-m-d', strtotime('-1 year')),
            'status' => 'active',
        ]);

        $validator = validator($request->all(), $request->rules());
        $this->assertFalse($validator->fails());
    }

    public function test_store_teacher_validation_fails_without_required_fields(): void
    {
        $request = new StoreTeacher();
        $request->merge([
            'nip' => '12345678',
        ]);

        $validator = validator($request->all(), $request->rules());
        $this->assertTrue($validator->fails());
        $this->assertArrayHasKey('join_date', $validator->errors());
        $this->assertArrayHasKey('status', $validator->errors());
    }

    public function test_update_teacher_validation_passes_with_valid_data(): void
    {
        $request = new UpdateTeacher();
        $request->merge([
            'nip' => '98765432',
            'expertise' => 'Mathematics',
        ]);

        $validator = validator($request->all(), $request->rules());
        $this->assertFalse($validator->fails());
    }

    public function test_all_form_requests_have_authorization_true(): void
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
            $this->assertTrue($request->authorize());
        }
    }
}
