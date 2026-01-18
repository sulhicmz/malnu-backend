<?php

declare(strict_types=1);

namespace Tests\Feature;

use Tests\TestCase;
use App\Services\TransportationManagementService;
use App\Models\Transportation\TransportationRoute;
use App\Models\Transportation\TransportationVehicle;
use App\Models\Transportation\TransportationDriver;
use App\Models\Transportation\TransportationFee;
use App\Models\Transportation\TransportationIncident;
use App\Models\SchoolManagement\Student;
use App\Models\User;

class TransportationManagementTest extends TestCase
{
    private TransportationManagementService $transportationService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->transportationService = $this->app->get(TransportationManagementService::class);
    }

    public function test_create_route(): void
    {
        $data = [
            'route_name' => 'Route 1',
            'route_description' => 'Morning route for North area',
            'start_location' => 'Main Gate',
            'end_location' => 'North Terminal',
            'start_time' => '07:00:00',
            'end_time' => '17:00:00',
            'distance_km' => 15.5,
            'stops' => [
                ['name' => 'Stop 1', 'time' => '07:00'],
                ['name' => 'Stop 2', 'time' => '07:15'],
            ],
            'created_by' => 'user-1',
        ];

        $route = $this->transportationService->createRoute($data);

        $this->assertInstanceOf(TransportationRoute::class, $route);
        $this->assertEquals('Route 1', $route->route_name);
        $this->assertEquals('active', $route->status);
    }

    public function test_create_vehicle(): void
    {
        $data = [
            'vehicle_number' => 'BUS-001',
            'license_plate' => 'B-1234-XYZ',
            'vehicle_type' => 'bus',
            'capacity' => 40,
            'make' => 'Mercedes',
            'model' => 'Sprinter',
            'year' => 2023,
            'status' => 'active',
            'created_by' => 'user-1',
        ];

        $vehicle = $this->transportationService->createVehicle($data);

        $this->assertInstanceOf(TransportationVehicle::class, $vehicle);
        $this->assertEquals('BUS-001', $vehicle->vehicle_number);
        $this->assertEquals(40, $vehicle->capacity);
    }

    public function test_create_driver(): void
    {
        $data = [
            'user_id' => 'user-1',
            'driver_license_number' => 'DL-2023-12345',
            'license_expiry_date' => '2025-12-31',
            'status' => 'active',
            'emergency_contact_phone' => '+62812345678',
            'created_by' => 'user-1',
        ];

        $driver = $this->transportationService->createDriver($data);

        $this->assertInstanceOf(TransportationDriver::class, $driver);
        $this->assertEquals('DL-2023-12345', $driver->driver_license_number);
        $this->assertEquals('active', $driver->status);
    }

    public function test_create_fee(): void
    {
        $student = Student::first();

        if (!$student) {
            $this->markTestSkipped('No student data available');
            return;
        }

        $data = [
            'student_id' => $student->id,
            'amount' => 500000,
            'currency' => 'IDR',
            'academic_year' => '2024/2025',
            'semester' => 'Semester 1',
            'due_date' => '2025-02-01',
            'payment_status' => 'unpaid',
            'created_by' => 'user-1',
        ];

        $fee = $this->transportationService->createFee($data);

        $this->assertInstanceOf(TransportationFee::class, $fee);
        $this->assertEquals(500000, $fee->amount);
        $this->assertEquals('unpaid', $fee->payment_status);
    }

    public function test_create_incident(): void
    {
        $route = TransportationRoute::first();

        if (!$route) {
            $this->markTestSkipped('No route data available');
            return;
        }

        $data = [
            'route_id' => $route->id,
            'incident_type' => 'delay',
            'incident_date' => date('Y-m-d H:i:s'),
            'description' => 'Bus arrived 15 minutes late due to traffic',
            'severity' => 'minor',
            'status' => 'open',
            'reported_by' => ['name' => 'Parent', 'phone' => '+62812345678'],
            'created_by' => 'user-1',
        ];

        $incident = $this->transportationService->createIncident($data);

        $this->assertInstanceOf(TransportationIncident::class, $incident);
        $this->assertEquals('delay', $incident->incident_type);
        $this->assertEquals('minor', $incident->severity);
        $this->assertEquals('open', $incident->status);
    }

    public function test_assign_student_to_route(): void
    {
        $student = Student::first();
        $route = TransportationRoute::first();

        if (!$student || !$route) {
            $this->markTestSkipped('No student or route data available');
            return;
        }

        $data = [
            'student_id' => $student->id,
            'route_id' => $route->id,
            'stop_id' => 'stop-1',
            'assignment_date' => date('Y-m-d'),
            'status' => 'active',
            'created_by' => 'user-1',
        ];

        $assignment = $this->transportationService->assignStudentToRoute($student->id, $route->id, $data);

        $this->assertEquals($student->id, $assignment->student_id);
        $this->assertEquals($route->id, $assignment->route_id);
        $this->assertEquals('active', $assignment->status);
    }

    public function test_update_route(): void
    {
        $route = TransportationRoute::first();

        if (!$route) {
            $this->markTestSkipped('No route data available');
            return;
        }

        $data = [
            'route_name' => 'Updated Route Name',
            'status' => 'inactive',
            'updated_by' => 'user-1',
        ];

        $updatedRoute = $this->transportationService->updateRoute($route->id, $data);

        $this->assertEquals('Updated Route Name', $updatedRoute->route_name);
        $this->assertEquals('inactive', $updatedRoute->status);
    }

    public function test_update_vehicle(): void
    {
        $vehicle = TransportationVehicle::first();

        if (!$vehicle) {
            $this->markTestSkipped('No vehicle data available');
            return;
        }

        $data = [
            'capacity' => 50,
            'status' => 'maintenance',
            'updated_by' => 'user-1',
        ];

        $updatedVehicle = $this->transportationService->updateVehicle($vehicle->id, $data);

        $this->assertEquals(50, $updatedVehicle->capacity);
        $this->assertEquals('maintenance', $updatedVehicle->status);
    }

    public function test_delete_route(): void
    {
        $route = TransportationRoute::first();

        if (!$route) {
            $this->markTestSkipped('No route data available');
            return;
        }

        $routeId = $route->id;
        $result = $this->transportationService->deleteRoute($routeId);

        $this->assertTrue($result);
        $this->assertNull(TransportationRoute::find($routeId));
    }

    public function test_get_student_registrations(): void
    {
        $student = Student::first();

        if (!$student) {
            $this->markTestSkipped('No student data available');
            return;
        }

        $result = $this->transportationService->getStudentRegistrations($student->id);

        $this->assertIsArray($result->registrations);
        $this->assertIsObject($result);
    }

    public function test_get_route_students(): void
    {
        $route = TransportationRoute::first();

        if (!$route) {
            $this->markTestSkipped('No route data available');
            return;
        }

        $result = $this->transportationService->getRouteStudents($route->id);

        $this->assertIsArray($result->students);
        $this->assertIsObject($result);
    }

    public function test_update_incident(): void
    {
        $incident = TransportationIncident::first();

        if (!$incident) {
            $this->markTestSkipped('No incident data available');
            return;
        }

        $data = [
            'status' => 'resolved',
            'resolution' => 'Issue resolved and parent informed',
            'resolved_date' => date('Y-m-d H:i:s'),
            'updated_by' => 'user-1',
        ];

        $updatedIncident = $this->transportationService->updateIncident($incident->id, $data);

        $this->assertEquals('resolved', $updatedIncident->status);
        $this->assertNotNull($updatedIncident->resolved_date);
    }
}
