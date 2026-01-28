<?php

declare(strict_types=1);

namespace Tests\Feature;

use Tests\TestCase;
use App\Services\TransportationService;
use App\Models\Transport\TransportVehicle;
use App\Models\Transport\TransportDriver;
use App\Models\Transport\TransportStop;
use App\Models\Transport\TransportRoute;
use App\Models\User;

class TransportationSystemTest extends TestCase
{
    private TransportationService $transportationService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->transportationService = new TransportationService();
    }

    public function test_create_vehicle()
    {
        $vehicleData = [
            'plate_number' => 'B1234CD',
            'vehicle_type' => 'bus',
            'capacity' => 40,
            'make' => 'Mercedes',
            'model' => 'Sprinter',
            'manufacture_year' => 2020,
            'registration_number' => 'REG123456',
            'registration_expiry' => '2026-12-31',
            'insurance_number' => 'INS789012',
            'insurance_expiry' => '2026-06-30',
            'status' => 'available',
        ];

        $vehicle = $this->transportationService->createVehicle($vehicleData);

        $this->assertInstanceOf(TransportVehicle::class, $vehicle);
        $this->assertEquals('B1234CD', $vehicle->plate_number);
        $this->assertEquals('bus', $vehicle->vehicle_type);
        $this->assertEquals(40, $vehicle->capacity);
    }

    public function test_get_vehicles()
    {
        $vehicleData = [
            'plate_number' => 'B1234CD',
            'vehicle_type' => 'bus',
            'capacity' => 40,
            'status' => 'available',
        ];

        $this->transportationService->createVehicle($vehicleData);

        $vehicles = $this->transportationService->getVehicles(['status' => 'available']);

        $this->assertIsArray($vehicles);
        $this->assertGreaterThan(0, count($vehicles));
    }

    public function test_create_driver()
    {
        $driverData = [
            'name' => 'John Driver',
            'license_number' => 'DL123456789',
            'license_expiry' => '2027-12-31',
            'phone' => '+62812345678',
            'certification_type' => 'Professional',
            'certification_expiry' => '2027-06-30',
            'status' => 'available',
        ];

        $driver = $this->transportationService->createDriver($driverData);

        $this->assertInstanceOf(TransportDriver::class, $driver);
        $this->assertEquals('John Driver', $driver->name);
        $this->assertEquals('DL123456789', $driver->license_number);
    }

    public function test_create_stop()
    {
        $stopData = [
            'name' => 'Main Gate',
            'address' => '123 School Street',
            'latitude' => -6.200000,
            'longitude' => 106.816666,
            'type' => 'pickup',
            'is_active' => true,
        ];

        $stop = $this->transportationService->createStop($stopData);

        $this->assertInstanceOf(TransportStop::class, $stop);
        $this->assertEquals('Main Gate', $stop->name);
        $this->assertEquals('123 School Street', $stop->address);
    }

    public function test_create_route()
    {
        $routeData = [
            'name' => 'Route A - North',
            'code' => 'RT-A-NORTH',
            'description' => 'Main route to northern areas',
            'start_time' => '07:00',
            'end_time' => '17:00',
            'capacity' => 40,
            'status' => 'active',
        ];

        $route = $this->transportationService->createRoute($routeData);

        $this->assertInstanceOf(TransportRoute::class, $route);
        $this->assertEquals('Route A - North', $route->name);
        $this->assertEquals('RT-A-NORTH', $route->code);
        $this->assertEquals(40, $route->capacity);
    }

    public function test_get_routes()
    {
        $routeData = [
            'name' => 'Route B - South',
            'code' => 'RT-B-SOUTH',
            'capacity' => 30,
            'status' => 'active',
        ];

        $this->transportationService->createRoute($routeData);

        $routes = $this->transportationService->getRoutes(['status' => 'active']);

        $this->assertIsArray($routes);
        $this->assertGreaterThan(0, count($routes));
    }

    public function test_add_stop_to_route()
    {
        $route = TransportRoute::create([
            'name' => 'Test Route',
            'code' => 'TEST-01',
            'capacity' => 20,
            'status' => 'active',
        ]);

        $stop = TransportStop::create([
            'name' => 'Test Stop',
            'address' => '123 Test Street',
            'latitude' => -6.200000,
            'longitude' => 106.816666,
        ]);

        $routeStop = $this->transportationService->addStopToRoute($route->id, $stop->id, 1, [
            'arrival_time' => '07:30',
            'fare' => 5000,
        ]);

        $this->assertEquals($route->id, $routeStop->route_id);
        $this->assertEquals($stop->id, $routeStop->stop_id);
        $this->assertEquals(1, $routeStop->stop_order);
        $this->assertEquals(5000, $routeStop->fare);
    }

    public function test_create_schedule()
    {
        $route = TransportRoute::create([
            'name' => 'Test Route',
            'code' => 'TEST-01',
            'capacity' => 20,
        ]);

        $vehicle = TransportVehicle::create([
            'plate_number' => 'B9999XX',
            'vehicle_type' => 'bus',
            'capacity' => 20,
        ]);

        $driver = TransportDriver::create([
            'name' => 'Test Driver',
            'license_number' => 'DL999999',
            'license_expiry' => '2027-12-31',
        ]);

        $scheduleData = [
            'route_id' => $route->id,
            'vehicle_id' => $vehicle->id,
            'driver_id' => $driver->id,
            'day_of_week' => 'Monday',
            'departure_time' => '07:00',
            'arrival_time' => '17:00',
        ];

        $schedule = $this->transportationService->createSchedule($scheduleData);

        $this->assertEquals($route->id, $schedule->route_id);
        $this->assertEquals($vehicle->id, $schedule->vehicle_id);
        $this->assertEquals($driver->id, $schedule->driver_id);
        $this->assertEquals('Monday', $schedule->day_of_week);
    }

    public function test_assign_student_to_route()
    {
        $route = TransportRoute::create([
            'name' => 'Test Route',
            'code' => 'TEST-01',
            'capacity' => 20,
        ]);

        $pickupStop = TransportStop::create([
            'name' => 'Pickup Stop',
            'address' => '123 Pickup St',
            'latitude' => -6.200000,
            'longitude' => 106.816666,
        ]);

        $dropoffStop = TransportStop::create([
            'name' => 'Dropoff Stop',
            'address' => '123 Dropoff St',
            'latitude' => -6.200000,
            'longitude' => 106.816666,
        ]);

        $assignment = $this->transportationService->assignStudentToRoute(
            'student-id-123',
            $route->id,
            [
                'pickup_stop_id' => $pickupStop->id,
                'dropoff_stop_id' => $dropoffStop->id,
                'session_type' => 'both',
                'fee' => 50000,
            ]
        );

        $this->assertEquals('student-id-123', $assignment->student_id);
        $this->assertEquals($route->id, $assignment->route_id);
        $this->assertEquals('both', $assignment->session_type);
    }

    public function test_record_attendance()
    {
        $route = TransportRoute::create([
            'name' => 'Test Route',
            'code' => 'TEST-01',
            'capacity' => 20,
        ]);

        $assignment = $this->transportationService->assignStudentToRoute(
            'student-id-123',
            $route->id
        );

        $attendanceData = [
            'status' => 'present',
            'boarding_time' => '07:15',
            'alighting_time' => '17:30',
        ];

        $attendance = $this->transportationService->recordAttendance(
            $assignment->id,
            $route->id,
            'student-id-123',
            $attendanceData
        );

        $this->assertEquals($assignment->id, $attendance->assignment_id);
        $this->assertEquals('student-id-123', $attendance->student_id);
        $this->assertEquals('present', $attendance->status);
        $this->assertEquals('07:15', $attendance->boarding_time);
    }

    public function test_record_tracking()
    {
        $vehicle = TransportVehicle::create([
            'plate_number' => 'B9999XX',
            'vehicle_type' => 'bus',
            'capacity' => 20,
        ]);

        $trackingData = [
            'latitude' => -6.200000,
            'longitude' => 106.816666,
            'speed' => 45.5,
            'heading' => 180.0,
            'odometer' => 15000.50,
        ];

        $tracking = $this->transportationService->recordTracking($vehicle->id, $trackingData);

        $this->assertEquals($vehicle->id, $tracking->vehicle_id);
        $this->assertEquals(-6.200000, $tracking->latitude);
        $this->assertEquals(106.816666, $tracking->longitude);
        $this->assertEquals(45.5, $tracking->speed);
    }

    public function test_report_incident()
    {
        $vehicle = TransportVehicle::create([
            'plate_number' => 'B9999XX',
            'vehicle_type' => 'bus',
            'capacity' => 20,
        ]);

        $incidentData = [
            'vehicle_id' => $vehicle->id,
            'incident_type' => 'breakdown',
            'severity' => 'high',
            'description' => 'Engine stopped unexpectedly',
            'location' => 'On Route A',
        ];

        $incident = $this->transportationService->reportIncident($incidentData);

        $this->assertEquals($vehicle->id, $incident->vehicle_id);
        $this->assertEquals('breakdown', $incident->incident_type);
        $this->assertEquals('high', $incident->severity);
        $this->assertEquals('open', $incident->status);
        $this->assertNotNull($incident->incident_time);
    }

    public function test_resolve_incident()
    {
        $incident = $this->transportationService->reportIncident([
            'incident_type' => 'breakdown',
            'severity' => 'high',
            'description' => 'Test incident',
        ]);

        $resolvedIncident = $this->transportationService->resolveIncident($incident->id, 'Fixed the engine');

        $this->assertEquals($incident->id, $resolvedIncident->id);
        $this->assertEquals('resolved', $resolvedIncident->status);
        $this->assertEquals('Fixed the engine', $resolvedIncident->resolution);
        $this->assertNotNull($resolvedIncident->resolved_at);
    }

    public function test_get_statistics()
    {
        $stats = $this->transportationService->getStatistics();

        $this->assertIsArray($stats);
        $this->assertArrayHasKey('total_vehicles', $stats);
        $this->assertArrayHasKey('total_drivers', $stats);
        $this->assertArrayHasKey('total_routes', $stats);
        $this->assertArrayHasKey('total_assignments', $stats);
        $this->assertArrayHasKey('open_incidents', $stats);
    }

    public function test_get_nearby_stops()
    {
        TransportStop::create([
            'name' => 'Stop A',
            'address' => 'Address A',
            'latitude' => -6.200000,
            'longitude' => 106.816666,
        ]);

        TransportStop::create([
            'name' => 'Stop B',
            'address' => 'Address B',
            'latitude' => -6.210000,
            'longitude' => 106.826666,
        ]);

        $nearbyStops = $this->transportationService->getNearbyStops(-6.200500, 106.817000, 2);

        $this->assertIsArray($nearbyStops);
        $this->assertGreaterThan(0, count($nearbyStops));
    }
}