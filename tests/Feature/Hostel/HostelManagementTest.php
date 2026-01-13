<?php

declare(strict_types=1);

namespace Tests\Feature\Hostel;

use Carbon\Carbon;
use Tests\TestCase;

class HostelManagementTest extends TestCase
{
    public function testCreateHostel()
    {
        $data = [
            'name' => 'Test Hostel',
            'code' => 'HOSTEL-001',
            'type' => 'boarding',
            'gender' => 'male',
            'capacity' => 100,
            'current_occupancy' => 0,
            'warden_name' => 'John Doe',
            'warden_contact' => '+1234567890',
            'address' => '123 Test Street',
            'is_active' => true
        ];

        $response = $this->post('/api/hostel/hostels', $data);

        $this->assertEquals(200, $response->getStatusCode());
    }

    public function testAssignStudentToRoom()
    {
        $data = [
            'student_id' => 'test-student-id',
            'hostel_id' => 'test-hostel-id',
            'room_id' => 'test-room-id',
            'assignment_date' => Carbon::now()->toDateString(),
            'bed_number' => 'A1',
            'status' => 'active'
        ];

        $response = $this->post('/api/hostel/assignments', $data);

        $this->assertEquals(200, $response->getStatusCode());
    }

    public function testCreateMaintenanceRequest()
    {
        $data = [
            'hostel_id' => 'test-hostel-id',
            'room_id' => 'test-room-id',
            'reported_by' => 'test-user-id',
            'type' => 'plumbing',
            'priority' => 'high',
            'description' => 'Leaking faucet in bathroom'
        ];

        $response = $this->post('/api/hostel/maintenance-requests', $data);

        $this->assertEquals(200, $response->getStatusCode());
    }

    public function testCreateVisitor()
    {
        $data = [
            'hostel_id' => 'test-hostel-id',
            'visitor_student_id' => 'test-student-id',
            'visitor_name' => 'Jane Doe',
            'visitor_phone' => '+9876543210',
            'relationship' => 'mother',
            'purpose' => 'Family visit',
            'id_proof_type' => 'ID Card',
            'id_proof_number' => 'ID123456'
        ];

        $response = $this->post('/api/hostel/visitors', $data);

        $this->assertEquals(200, $response->getStatusCode());
    }

    public function testCheckInStudent()
    {
        $data = [
            'student_id' => 'test-student-id',
            'hostel_id' => 'test-hostel-id',
            'room_id' => 'test-room-id',
            'attendance_date' => Carbon::now()->toDateString()
        ];

        $response = $this->post('/api/hostel/attendance/checkin', $data);

        $this->assertEquals(200, $response->getStatusCode());
    }

    public function testGetHostelOccupancy()
    {
        $hostelId = 'test-hostel-id';
        $response = $this->get('/api/hostel/hostels/' . $hostelId . '/occupancy');

        $this->assertEquals(200, $response->getStatusCode());
    }

    public function testGetStudentBoardingInfo()
    {
        $studentId = 'test-student-id';
        $response = $this->get('/api/hostel/students/' . $studentId . '/boarding-info');

        $this->assertEquals(200, $response->getStatusCode());
    }

    public function testGetAvailableRooms()
    {
        $hostelId = 'test-hostel-id';
        $response = $this->get('/api/hostel/hostels/' . $hostelId . '/available-rooms');

        $this->assertEquals(200, $response->getStatusCode());
    }
}
