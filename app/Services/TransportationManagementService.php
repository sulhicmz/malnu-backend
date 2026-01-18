<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Transportation\TransportationRoute;
use App\Models\Transportation\TransportationRegistration;
use App\Models\Transportation\TransportationVehicle;
use App\Models\Transportation\TransportationDriver;
use App\Models\Transportation\TransportationAssignment;
use App\Models\Transportation\TransportationFee;
use App\Models\Transportation\TransportationIncident;
use App\Models\SchoolManagement\Student;
use App\Models\User;

class TransportationManagementService
{
    public function createRoute(array $data): TransportationRoute
    {
        return TransportationRoute::create([
            'route_name' => $data['route_name'],
            'route_description' => $data['route_description'] ?? null,
            'start_location' => $data['start_location'],
            'end_location' => $data['end_location'],
            'status' => $data['status'] ?? 'active',
            'start_time' => $data['start_time'],
            'end_time' => $data['end_time'],
            'distance_km' => $data['distance_km'] ?? null,
            'fuel_capacity' => $data['fuel_capacity'] ?? null,
            'vehicle_id' => $data['vehicle_id'] ?? null,
            'driver_id' => $data['driver_id'] ?? null,
            'stops' => $data['stops'] ?? null,
            'created_by' => $data['created_by'],
            'updated_by' => $data['created_by'],
        ]);
    }

    public function updateRoute(string $routeId, array $data): TransportationRoute
    {
        $route = TransportationRoute::findOrFail($routeId);
        $route->update(array_merge($data, ['updated_by' => $data['updated_by']]));
        return $route->fresh();
    }

    public function deleteRoute(string $routeId): bool
    {
        return TransportationRoute::findOrFail($routeId)->delete();
    }

    public function createRegistration(array $data): TransportationRegistration
    {
        return TransportationRegistration::create([
            'student_id' => $data['student_id'],
            'route_id' => $data['route_id'] ?? null,
            'stop_id' => $data['stop_id'] ?? null,
            'pickup_location' => $data['pickup_location'] ?? null,
            'dropoff_location' => $data['dropoff_location'] ?? null,
            'registration_date' => $data['registration_date'] ?? date('Y-m-d'),
            'status' => $data['status'] ?? 'pending',
            'emergency_contact' => $data['emergency_contact'] ?? null,
            'notes' => $data['notes'] ?? null,
            'created_by' => $data['created_by'],
            'updated_by' => $data['created_by'],
        ]);
    }

    public function updateRegistration(string $registrationId, array $data): TransportationRegistration
    {
        $registration = TransportationRegistration::findOrFail($registrationId);
        $registration->update(array_merge($data, ['updated_by' => $data['updated_by']]));
        return $registration->fresh();
    }

    public function createVehicle(array $data): TransportationVehicle
    {
        return TransportationVehicle::create([
            'vehicle_number' => $data['vehicle_number'],
            'license_plate' => $data['license_plate'],
            'vehicle_type' => $data['vehicle_type'],
            'capacity' => $data['capacity'],
            'model' => $data['model'] ?? null,
            'make' => $data['make'] ?? null,
            'year' => $data['year'] ?? null,
            'status' => $data['status'] ?? 'active',
            'fuel_consumption' => $data['fuel_consumption'] ?? null,
            'last_maintenance_date' => $data['last_maintenance_date'] ?? null,
            'next_maintenance_date' => $data['next_maintenance_date'] ?? null,
            'notes' => $data['notes'] ?? null,
            'created_by' => $data['created_by'],
            'updated_by' => $data['created_by'],
        ]);
    }

    public function updateVehicle(string $vehicleId, array $data): TransportationVehicle
    {
        $vehicle = TransportationVehicle::findOrFail($vehicleId);
        $vehicle->update(array_merge($data, ['updated_by' => $data['updated_by']]));
        return $vehicle->fresh();
    }

    public function createDriver(array $data): TransportationDriver
    {
        return TransportationDriver::create([
            'user_id' => $data['user_id'],
            'driver_license_number' => $data['driver_license_number'],
            'license_expiry_date' => $data['license_expiry_date'],
            'status' => $data['status'] ?? 'active',
            'background_check_date' => $data['background_check_date'] ?? null,
            'emergency_contact_phone' => $data['emergency_contact_phone'] ?? null,
            'notes' => $data['notes'] ?? null,
            'created_by' => $data['created_by'],
            'updated_by' => $data['created_by'],
        ]);
    }

    public function updateDriver(string $driverId, array $data): TransportationDriver
    {
        $driver = TransportationDriver::findOrFail($driverId);
        $driver->update(array_merge($data, ['updated_by' => $data['updated_by']]));
        return $driver->fresh();
    }

    public function assignStudentToRoute(string $studentId, string $routeId, array $data): TransportationAssignment
    {
        return TransportationAssignment::create([
            'student_id' => $studentId,
            'route_id' => $routeId,
            'stop_id' => $data['stop_id'] ?? null,
            'assignment_date' => $data['assignment_date'] ?? date('Y-m-d'),
            'status' => $data['status'] ?? 'active',
            'notes' => $data['notes'] ?? null,
            'created_by' => $data['created_by'],
            'updated_by' => $data['created_by'],
        ]);
    }

    public function createFee(array $data): TransportationFee
    {
        return TransportationFee::create([
            'student_id' => $data['student_id'],
            'route_id' => $data['route_id'] ?? null,
            'amount' => $data['amount'],
            'currency' => $data['currency'] ?? 'IDR',
            'academic_year' => $data['academic_year'],
            'semester' => $data['semester'] ?? null,
            'due_date' => $data['due_date'],
            'paid_date' => $data['paid_date'] ?? null,
            'payment_status' => $data['payment_status'] ?? 'unpaid',
            'payment_method' => $data['payment_method'] ?? null,
            'transaction_id' => $data['transaction_id'] ?? null,
            'notes' => $data['notes'] ?? null,
            'created_by' => $data['created_by'],
            'updated_by' => $data['created_by'],
        ]);
    }

    public function updateFee(string $feeId, array $data): TransportationFee
    {
        $fee = TransportationFee::findOrFail($feeId);
        $fee->update(array_merge($data, ['updated_by' => $data['updated_by']]));
        return $fee->fresh();
    }

    public function createIncident(array $data): TransportationIncident
    {
        return TransportationIncident::create([
            'route_id' => $data['route_id'] ?? null,
            'vehicle_id' => $data['vehicle_id'] ?? null,
            'driver_id' => $data['driver_id'] ?? null,
            'student_id' => $data['student_id'] ?? null,
            'incident_type' => $data['incident_type'],
            'incident_date' => $data['incident_date'],
            'description' => $data['description'],
            'severity' => $data['severity'] ?? 'minor',
            'status' => $data['status'] ?? 'open',
            'resolution' => $data['resolution'] ?? null,
            'resolved_date' => $data['resolved_date'] ?? null,
            'reported_by' => $data['reported_by'] ?? null,
            'notes' => $data['notes'] ?? null,
            'created_by' => $data['created_by'],
            'updated_by' => $data['created_by'],
        ]);
    }

    public function updateIncident(string $incidentId, array $data): TransportationIncident
    {
        $incident = TransportationIncident::findOrFail($incidentId);
        $incident->update(array_merge($data, ['updated_by' => $data['updated_by']]));
        return $incident->fresh();
    }

    public function getStudentRegistrations(string $studentId): object
    {
        $registrations = TransportationRegistration::where('student_id', $studentId)
            ->with('route')
            ->orderBy('registration_date', 'desc')
            ->get();

        return (object) [
            'registrations' => $registrations,
        ];
    }

    public function getRouteStudents(string $routeId): object
    {
        $assignments = TransportationAssignment::where('route_id', $routeId)
            ->with('student')
            ->where('status', 'active')
            ->get();

        return (object) [
            'students' => $assignments,
            'total_count' => $assignments->count(),
        ];
    }

    public function getVehicleSchedule(string $vehicleId): object
    {
        $vehicle = TransportationVehicle::findOrFail($vehicleId);
        $routes = TransportationRoute::where('vehicle_id', $vehicleId)
            ->with(['driver', 'assignments'])
            ->get();

        return (object) [
            'vehicle' => $vehicle,
            'routes' => $routes,
        ];
    }

    public function getDriverSchedule(string $driverId): object
    {
        $routes = TransportationRoute::where('driver_id', $driverId)
            ->with(['vehicle', 'assignments'])
            ->where('status', 'active')
            ->get();

        return (object) [
            'routes' => $routes,
            'total_count' => $routes->count(),
        ];
    }
}
