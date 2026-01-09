<?php

namespace Tests\Feature;

use App\Models\Attendance\StaffAttendance;
use App\Models\Attendance\LeaveType;
use App\Models\SchoolManagement\Staff;
use App\Models\User;
use Tests\TestCase;

class StandardizedErrorHandlingTest extends TestCase
{
    public function test_staff_attendance_index_returns_standardized_response(): void
    {
        StaffAttendance::factory()->create();

        $response = $this->get('/api/staff-attendances');

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'success',
            'data',
            'message',
            'timestamp'
        ]);
        $this->assertTrue($response->json('success'));
    }

    public function test_staff_attendance_store_returns_validation_error(): void
    {
        $response = $this->post('/api/staff-attendances', []);

        $response->assertStatus(422);
        $response->assertJsonStructure([
            'success',
            'error' => [
                'message',
                'code',
                'details'
            ],
            'timestamp'
        ]);
        $this->assertFalse($response->json('success'));
        $this->assertEquals('VALIDATION_ERROR', $response->json('error.code'));
    }

    public function test_staff_attendance_show_returns_not_found_error(): void
    {
        $response = $this->get('/api/staff-attendances/999999');

        $response->assertStatus(404);
        $response->assertJsonStructure([
            'success',
            'error' => [
                'message',
                'code'
            ],
            'timestamp'
        ]);
        $this->assertFalse($response->json('success'));
        $this->assertEquals('NOT_FOUND', $response->json('error.code'));
    }

    public function test_staff_attendance_store_returns_duplicate_error(): void
    {
        $staff = Staff::factory()->create();
        StaffAttendance::create([
            'staff_id' => $staff->id,
            'attendance_date' => '2024-01-01',
            'status' => 'present',
        ]);

        $response = $this->post('/api/staff-attendances', [
            'staff_id' => $staff->id,
            'attendance_date' => '2024-01-01',
            'status' => 'present',
        ]);

        $response->assertStatus(400);
        $this->assertFalse($response->json('success'));
        $this->assertEquals('DUPLICATE_ATTENDANCE', $response->json('error.code'));
    }

    public function test_leave_type_index_returns_standardized_response(): void
    {
        LeaveType::factory()->create();

        $response = $this->get('/api/leave-types');

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'success',
            'data',
            'message',
            'timestamp'
        ]);
        $this->assertTrue($response->json('success'));
    }

    public function test_leave_type_store_returns_validation_error(): void
    {
        $response = $this->post('/api/leave-types', []);

        $response->assertStatus(422);
        $response->assertJsonStructure([
            'success',
            'error' => [
                'message',
                'code',
                'details'
            ],
            'timestamp'
        ]);
        $this->assertFalse($response->json('success'));
        $this->assertEquals('VALIDATION_ERROR', $response->json('error.code'));
    }

    public function test_leave_type_show_returns_not_found_error(): void
    {
        $response = $this->get('/api/leave-types/999999');

        $response->assertStatus(404);
        $response->assertJsonStructure([
            'success',
            'error' => [
                'message',
                'code'
            ],
            'timestamp'
        ]);
        $this->assertFalse($response->json('success'));
        $this->assertEquals('NOT_FOUND', $response->json('error.code'));
    }

    public function test_leave_type_destroy_with_associated_requests_returns_error(): void
    {
        $leaveType = LeaveType::factory()->create();
        $staff = Staff::factory()->create();
        
        \App\Models\Attendance\LeaveRequest::create([
            'staff_id' => $staff->id,
            'leave_type_id' => $leaveType->id,
            'start_date' => '2024-01-01',
            'end_date' => '2024-01-03',
            'total_days' => 3,
            'reason' => 'Test',
            'status' => 'pending',
        ]);

        $response = $this->delete("/api/leave-types/{$leaveType->id}");

        $response->assertStatus(400);
        $this->assertFalse($response->json('success'));
        $this->assertEquals('DELETE_ERROR', $response->json('error.code'));
    }

    public function test_all_responses_include_timestamp(): void
    {
        $response = $this->get('/api/leave-types');

        $response->assertStatus(200);
        $this->assertArrayHasKey('timestamp', $response->json());
        $this->assertMatchesRegularExpression('/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}:\d{2}[+-]\d{2}:\d{2}$/', $response->json('timestamp'));
    }

    public function test_all_responses_include_success_field(): void
    {
        $response = $this->get('/api/leave-types');

        $response->assertStatus(200);
        $this->assertArrayHasKey('success', $response->json());
        $this->assertIsBool($response->json('success'));
    }

    public function test_error_responses_include_error_object(): void
    {
        $response = $this->get('/api/leave-types/999999');

        $response->assertStatus(404);
        $json = $response->json();
        $this->assertArrayHasKey('error', $json);
        $this->assertArrayHasKey('message', $json['error']);
        $this->assertArrayHasKey('code', $json['error']);
    }
}
