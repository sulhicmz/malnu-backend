<?php

declare(strict_types=1);

namespace Tests\Feature;

use Tests\TestCase;

class TransportationManagementTest extends TestCase
{
    public function test_create_vehicle()
    {
        $response = $this->post('/api/transportation/vehicles', [
            'vehicle_number' => 'BUS-001',
            'license_plate' => 'ABC-1234',
            'type' => 'bus',
            'capacity' => 40,
            'make' => 'Toyota',
            'model' => 'Coaster',
            'year' => 2020,
            'status' => 'active',
        ]);

        $response->assertStatus(200);
        $this->assertArrayHasKey('success', $response->json());
        $this->assertTrue($response->json()['success']);
    }

    public function test_create_vehicle_validation()
    {
        $response = $this->post('/api/transportation/vehicles', [
            'type' => 'bus',
        ]);

        $response->assertStatus(400);
        $this->assertFalse($response->json()['success']);
    }

    public function test_get_all_vehicles()
    {
        $response = $this->get('/api/transportation/vehicles');
        $response->assertStatus(200);
        $this->assertArrayHasKey('success', $response->json());
    }

    public function test_create_stop()
    {
        $response = $this->post('/api/transportation/stops', [
            'name' => 'Central Station',
            'address' => '123 Main Street',
            'latitude' => 40.7128,
            'longitude' => -74.0060,
            'is_active' => true,
        ]);

        $response->assertStatus(200);
        $this->assertTrue($response->json()['success']);
    }

    public function test_create_stop_validation()
    {
        $response = $this->post('/api/transportation/stops', [
            'name' => 'Test Stop',
        ]);

        $response->assertStatus(400);
    }

    public function test_create_route()
    {
        $response = $this->post('/api/transportation/routes', [
            'route_number' => 'R001',
            'name' => 'Downtown Express',
            'start_location' => 'Central Station',
            'end_location' => 'School Campus',
            'departure_time' => '07:00:00',
            'arrival_time' => '08:00:00',
            'status' => 'active',
            'capacity' => 40,
        ]);

        $response->assertStatus(200);
        $this->assertTrue($response->json()['success']);
    }

    public function test_create_driver()
    {
        $response = $this->post('/api/transportation/drivers', [
            'name' => 'John Doe',
            'phone' => '+1234567890',
            'license_number' => 'LIC-12345',
            'license_expiry' => '2025-12-31',
            'license_type' => 'commercial',
            'status' => 'active',
        ]);

        $response->assertStatus(200);
        $this->assertTrue($response->json()['success']);
    }

    public function test_create_schedule()
    {
        $response = $this->post('/api/transportation/schedules', [
            'route_id' => 'test-route-id',
            'vehicle_id' => 'test-vehicle-id',
            'driver_id' => 'test-driver-id',
            'shift' => 'morning',
            'day_type' => 'weekday',
            'effective_start_date' => '2025-01-01',
            'status' => 'active',
        ]);

        $response->assertStatus(200);
    }

    public function test_create_assignment()
    {
        $response = $this->post('/api/transportation/assignments', [
            'route_id' => 'test-route-id',
            'student_id' => 'test-student-id',
            'trip_type' => 'both',
            'start_date' => '2025-01-01',
            'status' => 'active',
        ]);

        $response->assertStatus(200);
    }

    public function test_duplicate_assignment_throws_exception()
    {
        $this->post('/api/transportation/assignments', [
            'route_id' => 'test-route-id',
            'student_id' => 'test-student-id',
            'trip_type' => 'both',
            'start_date' => '2025-01-01',
            'status' => 'active',
        ]);

        $response = $this->post('/api/transportation/assignments', [
            'route_id' => 'test-route-id',
            'student_id' => 'test-student-id',
            'trip_type' => 'both',
            'start_date' => '2025-01-01',
            'status' => 'active',
        ]);

        $response->assertStatus(500);
    }

    public function test_record_attendance()
    {
        $response = $this->post('/api/transportation/attendance', [
            'assignment_id' => 'test-assignment-id',
            'student_id' => 'test-student-id',
            'route_id' => 'test-route-id',
            'trip_type' => 'morning',
            'attendance_date' => '2025-01-01',
            'pickup_status' => 'present',
        ]);

        $response->assertStatus(200);
    }

    public function test_create_fee()
    {
        $response = $this->post('/api/transportation/fees', [
            'student_id' => 'test-student-id',
            'amount' => 100.00,
            'fee_type' => 'monthly',
            'due_date' => '2025-02-01',
            'status' => 'pending',
        ]);

        $response->assertStatus(200);
    }

    public function test_mark_fee_paid()
    {
        $feeId = 'test-fee-id';
        
        $response = $this->post("/api/transportation/fees/{$feeId}/pay", [
            'payment_method' => 'cash',
            'transaction_reference' => 'TXN-12345',
        ]);

        $response->assertStatus(200);
    }

    public function test_create_bus_delay_notification()
    {
        $response = $this->post('/api/transportation/notifications/delay', [
            'route_id' => 'test-route-id',
            'message' => 'Bus is running 15 minutes late due to traffic',
            'priority' => 'high',
        ]);

        $response->assertStatus(200);
    }

    public function test_create_emergency_notification()
    {
        $response = $this->post('/api/transportation/notifications/emergency', [
            'route_id' => 'test-route-id',
            'message' => 'Emergency: Bus breakdown at location X',
        ]);

        $response->assertStatus(200);
        $this->assertEquals('urgent', $response->json()['data']['priority']);
    }

    public function test_get_vehicle_occupancy()
    {
        $routeId = 'test-route-id';
        $response = $this->get("/api/transportation/reports/occupancy/{$routeId}");

        $response->assertStatus(200);
        $this->assertArrayHasKey('capacity', $response->json()['data']);
        $this->assertArrayHasKey('assigned_students', $response->json()['data']);
        $this->assertArrayHasKey('available_seats', $response->json()['data']);
    }

    public function test_get_transportation_report()
    {
        $response = $this->get('/api/transportation/reports/summary');

        $response->assertStatus(200);
        $this->assertArrayHasKey('active_vehicles', $response->json()['data']);
        $this->assertArrayHasKey('active_routes', $response->json()['data']);
        $this->assertArrayHasKey('students_transportation', $response->json()['data']);
    }
}
