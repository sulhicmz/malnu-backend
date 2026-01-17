<?php

declare(strict_types=1);

namespace Tests\Feature;

use HyperfTest\Http\Coroutine;
use PHPUnit\Framework\TestCase;
use Hyperf\DbConnection\Db;
use App\Models\Transportation\TransportationRoute;
use App\Models\Transportation\TransportationRegistration;
use App\Models\Transportation\TransportationVehicle;
use App\Models\Transportation\TransportationDriver;
use App\Models\Transportation\TransportationFee;
use App\Models\Transportation\TransportationIncident;
use App\Services\TransportationManagementService;

class TransportationManagementTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new TransportationManagementService();
    }

    public function testGetAllRoutes()
    {
        $routes = $this->service->getAllRoutes();
        
        $this->assertIsArray($routes);
        $this->assertCount(0, $routes);
    }

    public function testCreateRoute()
    {
        $data = [
            'route_name' => 'Test Route',
            'origin' => 'School A',
            'destination' => 'School B',
            'departure_time' => '07:00',
            'arrival_time' => '08:00',
            'capacity' => 50,
            'bus_number' => 'BUS-001',
        ];

        $route = $this->service->createRoute($data);

        $this->assertIsArray($route);
        $this->assertEquals('Test Route', $route->route_name);
        $this->assertEquals('School A', $route->origin);
        $this->assertEquals('School B', $route->destination);
        $this->assertEquals('07:00', $route->departure_time);
        $this->assertEquals('08:00', $route->arrival_time);
        $this->assertEquals(50, $route->capacity);
        $this->assertEquals('BUS-001', $route->bus_number);
        $this->assertEquals('active', $route->status);
    }

    public function testCreateRouteWithMissingRequiredFields()
    {
        $data = [
            'origin' => 'School A',
        ];

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('The route_name field is required.');
        $this->service->createRoute($data);
    }

    public function testUpdateRoute()
    {
        $route = TransportationRoute::create([
            'route_name' => 'Original Route',
            'status' => 'active',
        ]);

        $updateData = [
            'route_name' => 'Updated Route',
            'capacity' => 40,
        ];

        $updatedRoute = $this->service->updateRoute($route->id, $updateData);

        $this->assertEquals('Updated Route', $updatedRoute->route_name);
        $this->assertEquals(40, $updatedRoute->capacity);
    }

    public function testDeleteRoute()
    {
        $route = TransportationRoute::create([
            'route_name' => 'Test Route',
            'status' => 'active',
        ]);

        $this->service->deleteRoute($route->id);

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Route not found');
    }

    public function testRegisterStudent()
    {
        $route = TransportationRoute::create([
            'route_name' => 'Test Route',
            'capacity' => 50,
            'status' => 'active',
        ]);

        $registrationData = [
            'fee_amount' => 50000,
        ];

        $registration = $this->service->registerStudent('student-123', $route->id, $registrationData);

        $this->assertIsArray($registration);
        $this->assertEquals('student-123', $registration->student_id);
        $this->assertEquals($route->id, $registration->route_id);
        $this->assertEquals('50000', $registration->fee_amount);
        $this->assertEquals('active', $registration->status);
    }

    public function testRegisterStudentWhenRouteAtCapacity()
    {
        $route = TransportationRoute::create([
            'route_name' => 'Full Route',
            'capacity' => 50,
            'current_enrollment' => 50,
            'status' => 'active',
        ]);

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Route is at full capacity');
    }

    public function testGetAvailableVehicles()
    {
        $vehicles = $this->service->getAvailableVehicles();

        $this->assertIsArray($vehicles);
        $this->assertCount(0, $vehicles);
    }

    public function testCreateVehicle()
    {
        $data = [
            'vehicle_number' => 'VEH-001',
            'license_plate' => 'ABC-123',
            'capacity' => 50,
            'vehicle_type' => 'bus',
        ];

        $vehicle = $this->service->createVehicle($data);

        $this->assertIsArray($vehicle);
        $this->assertEquals('VEH-001', $vehicle->vehicle_number);
        $this->assertEquals('ABC-123', $vehicle->license_plate);
        $this->assertEquals('bus', $vehicle->vehicle_type);
        $this->assertEquals(50, $vehicle->capacity);
        $this->assertEquals('active', $vehicle->status);
    }

    public function testCreateVehicleWithMissingRequiredFields()
    {
        $data = [
            'capacity' => 50,
        ];

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('The vehicle_number field is required.');
        $this->service->createVehicle($data);
    }

    public function testCreateDriver()
    {
        $data = [
            'driver_name' => 'Test Driver',
            'license_number' => 'DRV-001',
            'phone_number' => '6281234567',
            'email' => 'driver@test.com',
        ];

        $driver = $this->service->createDriver($data);

        $this->assertIsArray($driver);
        $this->assertEquals('Test Driver', $driver->driver_name);
        $this->assertEquals('DRV-001', $driver->license_number);
        $this->assertEquals('6281234567', $driver->phone_number);
        $this->assertEquals('driver@test.com', $driver->email);
        $this->assertEquals('active', $driver->status);
    }

    public function testCreateDriverWithMissingRequiredFields()
    {
        $data = [
            'phone_number' => '6281234567',
        ];

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('The driver_name field is required.');
        $this->service->createDriver($data);
    }

    public function testAssignDriver()
    {
        $registration = TransportationRegistration::create([
            'student_id' => 'student-123',
            'route_id' => 'route-001',
            'status' => 'active',
        ]);

        $assignmentData = [
            'driver_id' => 'driver-001',
            'vehicle_id' => 'vehicle-001',
        ];

        $assignment = $this->service->assignDriver($registration->id, $assignmentData);

        $this->assertIsArray($assignment);
        $this->assertEquals($registration->id, $assignment->registration_id);
    }

    public function testCreateFee()
    {
        $registration = TransportationRegistration::create([
            'student_id' => 'student-123',
            'route_id' => 'route-001',
            'status' => 'active',
        ]);

        $feeData = [
            'amount' => 50000,
            'fee_type' => 'transportation',
        ];

        $fee = $this->service->createFee($registration->id, $feeData);

        $this->assertIsArray($fee);
        $this->assertEquals($registration->id, $fee->registration_id);
        $this->assertEquals(50000, $fee->amount);
        $this->assertEquals('transportation', $fee->fee_type);
        $this->assertEquals('pending', $fee->payment_status);
    }

    public function testMarkFeePaid()
    {
        $registration = TransportationRegistration::create([
            'student_id' => 'student-123',
            'route_id' => 'route-001',
            'status' => 'active',
        ]);

        $fee = TransportationFee::create([
            'registration_id' => $registration->id,
            'payment_status' => 'pending',
            'amount' => 50000,
        ]);

        $paidFee = $this->service->markFeePaid($fee->id);

        $this->assertEquals('paid', $paidFee->payment_status);
        $this->assertNotNull($paidFee->paid_date);
    }

    public function testCreateIncident()
    {
        $data = [
            'incident_type' => 'delay',
            'severity' => 'low',
            'description' => 'Bus arrived late',
        ];

        $incident = $this->service->createIncident($data);

        $this->assertIsArray($incident);
        $this->assertEquals('delay', $incident->incident_type);
        $this->assertEquals('low', $incident->severity);
        $this->assertEquals('Bus arrived late', $incident->description);
        $this->assertEquals('reported', $incident->status);
    }

    public function testUpdateIncident()
    {
        $incident = TransportationIncident::create([
            'incident_type' => 'accident',
            'severity' => 'medium',
            'status' => 'reported',
        ]);

        $updateData = [
            'status' => 'investigating',
            'action_taken' => 'Maintenance check scheduled',
        ];

        $updatedIncident = $this->service->updateIncident($incident->id, $updateData);

        $this->assertEquals('investigating', $updatedIncident->status);
        $this->assertEquals('Maintenance check scheduled', $updatedIncident->action_taken);
    }

    public function testGetStudentRegistrations()
    {
        $route = TransportationRoute::create([
            'route_name' => 'Test Route',
            'capacity' => 50,
            'status' => 'active',
        ]);

        $registration = TransportationRegistration::create([
            'student_id' => 'student-123',
            'route_id' => $route->id,
            'status' => 'active',
        ]);

        $registrations = $this->service->getStudentRegistrations('student-123');

        $this->assertIsArray($registrations);
        $this->assertCount(1, $registrations);
    }

    public function testGetRouteStatistics()
    {
        $route = TransportationRoute::create([
            'route_name' => 'Test Route',
            'capacity' => 50,
            'status' => 'active',
        ]);

        $registration = TransportationRegistration::create([
            'student_id' => 'student-123',
            'route_id' => $route->id,
            'status' => 'active',
            'fee_amount' => 50000,
        ]);

        TransportationFee::create([
            'registration_id' => $registration->id,
            'amount' => 50000,
            'payment_status' => 'paid',
        ]);

        $statistics = $this->service->getRouteStatistics($route->id);

        $this->assertIsArray($statistics);
        $this->assertArrayHasKey('route', $statistics);
        $this->assertEquals(1, $statistics['active_registrations']);
    }
}
