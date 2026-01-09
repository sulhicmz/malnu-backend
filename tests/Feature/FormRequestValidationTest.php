<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Http\Requests\LeaveRequest\StoreLeaveRequest;
use App\Http\Requests\LeaveRequest\UpdateLeaveRequest;
use App\Http\Requests\LeaveRequest\ApproveLeaveRequest;
use App\Http\Requests\SchoolManagement\StoreStudent;
use App\Http\Requests\SchoolManagement\UpdateStudent;
use App\Http\Requests\SchoolManagement\StoreTeacher;
use App\Http\Requests\SchoolManagement\UpdateTeacher;

class FormRequestValidationTest extends TestCase
{
    public function test_store_leave_request_validation()
    {
        $request = new StoreLeaveRequest();

        $this->assertEquals([
            'staff_id' => 'required|integer|exists:staff,id',
            'leave_type_id' => 'required|integer|exists:leave_types,id',
            'start_date' => 'required|date|date_format:Y-m-d',
            'end_date' => 'required|date|date_format:Y-m-d|after_or_equal:start_date',
            'reason' => 'required|string|max:500',
        ], $request->rules());

        $this->assertEquals(true, $request->authorize());
    }

    public function test_update_leave_request_validation()
    {
        $request = new UpdateLeaveRequest();

        $this->assertEquals([
            'comments' => 'sometimes|string|nullable',
        ], $request->rules());

        $this->assertEquals(true, $request->authorize());
    }

    public function test_approve_leave_request_validation()
    {
        $request = new ApproveLeaveRequest();

        $this->assertEquals([
            'approval_comments' => 'sometimes|string|nullable',
        ], $request->rules());

        $this->assertEquals(true, $request->authorize());
    }

    public function test_store_student_validation()
    {
        $request = new StoreStudent();

        $this->assertEquals([
            'name' => 'required|string|max:255',
            'nisn' => 'required|string|max:20|unique:students,nisn',
            'email' => 'sometimes|nullable|email|max:255|unique:students,email',
            'class_id' => 'required|integer|exists:classes,id',
            'enrollment_year' => 'required|integer|digits:4',
            'status' => 'required|string|in:active,inactive,graduated,suspended',
            'phone' => 'sometimes|nullable|string|max:20',
            'address' => 'sometimes|nullable|string|max:500',
            'date_of_birth' => 'sometimes|nullable|date|date_format:Y-m-d',
            'gender' => 'sometimes|nullable|string|in:male,female',
            'parent_name' => 'sometimes|nullable|string|max:255',
            'parent_phone' => 'sometimes|nullable|string|max:20',
        ], $request->rules());

        $this->assertEquals(true, $request->authorize());
    }

    public function test_update_student_validation()
    {
        $request = new UpdateStudent();

        $this->assertEquals([
            'name' => 'sometimes|string|max:255',
            'nisn' => 'sometimes|string|max:20',
            'email' => 'sometimes|nullable|email|max:255',
            'class_id' => 'sometimes|integer|exists:classes,id',
            'enrollment_year' => 'sometimes|integer|digits:4',
            'status' => 'sometimes|string|in:active,inactive,graduated,suspended',
            'phone' => 'sometimes|nullable|string|max:20',
            'address' => 'sometimes|nullable|string|max:500',
            'date_of_birth' => 'sometimes|nullable|date|date_format:Y-m-d',
            'gender' => 'sometimes|nullable|string|in:male,female',
            'parent_name' => 'sometimes|nullable|string|max:255',
            'parent_phone' => 'sometimes|nullable|string|max:20',
        ], $request->rules());

        $this->assertEquals(true, $request->authorize());
    }

    public function test_store_teacher_validation()
    {
        $request = new StoreTeacher();

        $this->assertEquals([
            'name' => 'required|string|max:255',
            'nip' => 'required|string|max:20|unique:teachers,nip',
            'email' => 'sometimes|nullable|email|max:255|unique:teachers,email',
            'subject_id' => 'required|integer|exists:subjects,id',
            'class_id' => 'sometimes|nullable|integer|exists:classes,id',
            'join_date' => 'required|date|date_format:Y-m-d',
            'status' => 'sometimes|string|in:active,inactive,on_leave,resigned',
            'phone' => 'sometimes|nullable|string|max:20',
            'address' => 'sometimes|nullable|string|max:500',
            'date_of_birth' => 'sometimes|nullable|date|date_format:Y-m-d',
            'gender' => 'sometimes|nullable|string|in:male,female',
            'education_level' => 'sometimes|nullable|string|max:255',
            'specialization' => 'sometimes|nullable|string|max:255',
        ], $request->rules());

        $this->assertEquals(true, $request->authorize());
    }

    public function test_update_teacher_validation()
    {
        $request = new UpdateTeacher();

        $this->assertEquals([
            'name' => 'sometimes|string|max:255',
            'nip' => 'sometimes|string|max:20',
            'email' => 'sometimes|nullable|email|max:255',
            'subject_id' => 'sometimes|integer|exists:subjects,id',
            'class_id' => 'sometimes|nullable|integer|exists:classes,id',
            'join_date' => 'sometimes|date|date_format:Y-m-d',
            'status' => 'sometimes|string|in:active,inactive,on_leave,resigned',
            'phone' => 'sometimes|nullable|string|max:20',
            'address' => 'sometimes|nullable|string|max:500',
            'date_of_birth' => 'sometimes|nullable|date|date_format:Y-m-d',
            'gender' => 'sometimes|nullable|string|in:male,female',
            'education_level' => 'sometimes|nullable|string|max:255',
            'specialization' => 'sometimes|nullable|string|max:255',
        ], $request->rules());

        $this->assertEquals(true, $request->authorize());
    }
}
