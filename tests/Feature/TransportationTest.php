<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Transportation\TransportVehicle;
use App\Models\Transportation\TransportDriver;
use App\Models\Transportation\TransportRoute;
use App\Models\Transportation\TransportStop;
use App\Models\Transportation\TransportAssignment;
use App\Models\Transportation\TransportAttendance;
use App\Models\Transportation\TransportIncident;
use App\Models\Transportation\TransportTracking;
use App\Models\User;
use Tymon\JWTAuth\Facades\JWTAuth;

class TransportationTest extends TestCase
{
    protected $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
        $this->user->assignRole('Super Admin');
    }

    public function test_create_vehicle()
    {
        $token = JWTAuth::fromUser($this->user);

        $vehicleData = [
            'plate_number' => 'B 1234 CD',
            'vehicle_type' => 'bus',
            'make' => 'Mercedes',
            'model' => 'Sprinter',
            'year' => 2020,
            'capacity' => 20,
            'fuel_type' => 'diesel',
        ];

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->postJson('/api/transport/vehicles', $vehicleData);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Vehicle created successfully'
            ]);

        $this->assertDatabaseHas('transport_vehicles', [
            'plate_number' => 'B 1234 CD',
            'vehicle_type' => 'bus',
        ]);
    }

    public function test_create_vehicle_validation()
    {
        $token = JWTAuth::fromUser($this->user);

        $vehicleData = [
            'plate_number' => '',
            'vehicle_type' => 'invalid',
        ];

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->postJson('/api/transport/vehicles', $vehicleData);

        $response->assertStatus(422)
            ->assertJson([
                'success' => false,
                'error' => [
                    'code' => 'VALIDATION_ERROR',
                ]
            ]);
    }

    public function test_list_vehicles()
    {
        $token = JWTAuth::fromUser($this->user);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->getJson('/api/transport/vehicles');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data',
                'message',
                'timestamp'
            ]);
    }

    public function test_get_vehicle()
    {
        $token = JWTAuth::fromUser($this->user);

        $vehicle = TransportVehicle::create([
            'plate_number' => 'B 5678 EF',
            'vehicle_type' => 'bus',
            'make' => 'Mercedes',
            'model' => 'Sprinter',
            'capacity' => 20,
            'fuel_type' => 'diesel',
            'created_by' => $this->user->id,
        ]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->getJson("/api/transport/vehicles/{$vehicle->id}");

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'id' => $vehicle->id,
                    'plate_number' => 'B 5678 EF',
                ]
            ]);
    }

    public function test_create_driver()
    {
        $token = JWTAuth::fromUser($this->user);

        $driverData = [
            'name' => 'John Driver',
            'phone' => '+62812345678',
            'license_number' => 'LIC123456',
            'license_type' => 'commercial',
            'license_expiry' => '2025-12-31',
            'hire_date' => '2024-01-01',
        ];

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->postJson('/api/transport/drivers', $driverData);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Driver created successfully'
            ]);

        $this->assertDatabaseHas('transport_drivers', [
            'name' => 'John Driver',
            'license_number' => 'LIC123456',
        ]);
    }

    public function test_list_drivers()
    {
        $token = JWTAuth::fromUser($this->user);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->getJson('/api/transport/drivers');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data',
                'message',
                'timestamp'
            ]);
    }

    public function test_create_route_with_stops()
    {
        $token = JWTAuth::fromUser($this->user);

        $routeData = [
            'route_name' => 'Route A',
            'route_number' => 'R001',
            'description' => 'Main school route',
            'start_time' => '06:30:00',
            'end_time' => '07:30:00',
            'stops' => [
                [
                    'stop_name' => 'Stop 1',
                    'latitude' => -6.2088,
                    'longitude' => 106.8456,
                ],
                [
                    'stop_name' => 'Stop 2',
                    'latitude' => -6.2090,
                    'longitude' => 106.8470,
                ],
            ],
        ];

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->postJson('/api/transport/routes', $routeData);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Route created successfully'
            ]);

        $this->assertDatabaseHas('transport_routes', [
            'route_name' => 'Route A',
            'route_number' => 'R001',
        ]);
    }

    public function test_list_routes()
    {
        $token = JWTAuth::fromUser($this->user);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->getJson('/api/transport/routes');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data',
                'message',
                'timestamp'
            ]);
    }

    public function test_record_location()
    {
        $token = JWTAuth::fromUser($this->user);

        $vehicle = TransportVehicle::create([
            'plate_number' => 'B 9999 XX',
            'vehicle_type' => 'bus',
            'make' => 'Toyota',
            'model' => 'Coaster',
            'capacity' => 25,
            'fuel_type' => 'diesel',
        ]);

        $locationData = [
            'vehicle_id' => $vehicle->id,
            'latitude' => -6.2088,
            'longitude' => 106.8456,
            'speed' => 40,
            'heading' => 90,
            'ignition_on' => 1,
        ];

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->postJson('/api/transport/tracking/location', $locationData);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Location recorded successfully'
            ]);

        $this->assertDatabaseHas('transport_tracking', [
            'vehicle_id' => $vehicle->id,
            'latitude' => -6.2088,
            'longitude' => 106.8456,
        ]);
    }

    public function test_get_vehicle_location()
    {
        $token = JWTAuth::fromUser($this->user);

        $vehicle = TransportVehicle::create([
            'plate_number' => 'B 8888 YY',
            'vehicle_type' => 'bus',
            'make' => 'Hino',
            'model' => 'RM300',
            'capacity' => 30,
            'fuel_type' => 'diesel',
        ]);

        TransportTracking::create([
            'vehicle_id' => $vehicle->id,
            'latitude' => -6.2100,
            'longitude' => 106.8500,
            'speed' => 30,
            'status' => 'moving',
            'recorded_at' => now(),
        ]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->getJson("/api/transport/tracking/vehicles/{$vehicle->id}/location");

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    'id',
                    'latitude',
                    'longitude',
                    'speed',
                    'status',
                    'recorded_at',
                ],
                'message',
                'timestamp'
            ]);
    }

    public function test_report_incident()
    {
        $token = JWTAuth::fromUser($this->user);

        $vehicle = TransportVehicle::create([
            'plate_number' => 'B 7777 ZZ',
            'vehicle_type' => 'bus',
            'make' => 'Isuzu',
            'model' => 'NQR',
            'capacity' => 20,
            'fuel_type' => 'diesel',
        ]);

        $incidentData = [
            'vehicle_id' => $vehicle->id,
            'incident_type' => 'breakdown',
            'severity' => 'moderate',
            'incident_time' => now()->toDateString(),
            'description' => 'Engine broke down near main road',
        ];

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->postJson('/api/transport/incidents', $incidentData);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Incident reported successfully'
            ]);

        $this->assertDatabaseHas('transport_incidents', [
            'vehicle_id' => $vehicle->id,
            'incident_type' => 'breakdown',
            'severity' => 'moderate',
        ]);
    }

    public function test_get_transportation_stats()
    {
        $token = JWTAuth::fromUser($this->user);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->getJson('/api/transport/stats');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    'total_vehicles',
                    'total_drivers',
                    'total_routes',
                    'total_assignments',
                    'vehicles_on_route',
                    'students_today',
                    'missed_today',
                    'open_incidents',
                ],
                'message',
                'timestamp'
            ]);
    }
}
