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
use Hyperf\Database\Model\Model;

class TransportationManagementService
{
    public function getAllRoutes(array $filters = [])
    {
        $query = TransportationRoute::query();

        if (! empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        return $query->orderBy('route_name')->get();
    }

    public function createRoute(array $data): TransportationRoute
    {
        return TransportationRoute::create([
            'route_name' => $data['route_name'],
            'route_description' => $data['route_description'] ?? null,
            'origin' => $data['origin'],
            'destination' => $data['destination'],
            'stops' => $data['stops'] ?? [],
            'departure_time' => $data['departure_time'] ?? null,
            'arrival_time' => $data['arrival_time'] ?? null,
            'capacity' => $data['capacity'] ?? 50,
            'bus_number' => $data['bus_number'] ?? null,
            'status' => $data['status'] ?? 'active',
        ]);
    }

    public function updateRoute(string $id, array $data): TransportationRoute
    {
        $route = TransportationRoute::find($id);
        if (! $route) {
            throw new \Exception('Route not found');
        }

        $route->update($data);
        return $route;
    }

    public function deleteRoute(string $id): void
    {
        $route = TransportationRoute::find($id);
        if (! $route) {
            throw new \Exception('Route not found');
        }

        $route->delete();
    }

    public function registerStudent(string $studentId, string $routeId, array $data): TransportationRegistration
    {
        $route = TransportationRoute::find($routeId);
        if (! $route) {
            throw new \Exception('Route not found');
        }

        if ($route->current_enrollment >= $route->capacity) {
            throw new \Exception('Route is at full capacity');
        }

        return TransportationRegistration::create([
            'student_id' => $studentId,
            'route_id' => $routeId,
            'registration_date' => now()->toDateString(),
            'expiry_date' => $data['expiry_date'] ?? null,
            'status' => 'active',
            'fee_amount' => $data['fee_amount'] ?? 0,
            'special_requirements' => $data['special_requirements'] ?? null,
            'parent_notes' => $data['parent_notes'] ?? null,
        ]);
    }

    public function assignDriver(string $registrationId, string $driverId, string $vehicleId, array $data): TransportationAssignment
    {
        $assignment = TransportationAssignment::create([
            'registration_id' => $registrationId,
            'driver_id' => $driverId,
            'vehicle_id' => $vehicleId,
            'assignment_date' => now()->toDateString(),
            'assignment_type' => $data['assignment_type'] ?? 'route',
            'notes' => $data['notes'] ?? null,
        ]);

        $registration = TransportationRegistration::find($registrationId);
        if ($registration) {
            $registration->update(['bus_stop_id' => $data['bus_stop_id'] ?? null]);
        }

        return $assignment;
    }

    public function createVehicle(array $data): TransportationVehicle
    {
        return TransportationVehicle::create([
            'vehicle_number' => $data['vehicle_number'],
            'vehicle_type' => $data['vehicle_type'] ?? 'bus',
            'license_plate' => $data['license_plate'],
            'capacity' => $data['capacity'] ?? 50,
            'make' => $data['make'] ?? null,
            'model' => $data['model'] ?? null,
            'year' => $data['year'] ?? null,
            'status' => 'active',
            'insurance_number' => $data['insurance_number'] ?? null,
            'insurance_expiry' => $data['insurance_expiry'] ?? null,
            'notes' => $data['notes'] ?? null,
        ]);
    }

    public function createDriver(array $data): TransportationDriver
    {
        return TransportationDriver::create([
            'driver_name' => $data['driver_name'],
            'phone_number' => $data['phone_number'],
            'email' => $data['email'] ?? null,
            'license_number' => $data['license_number'],
            'license_expiry' => $data['license_expiry'] ?? null,
            'status' => 'active',
            'certifications' => $data['certifications'] ?? null,
        ]);
    }

    public function createFee(string $registrationId, array $data): TransportationFee
    {
        return TransportationFee::create([
            'registration_id' => $registrationId,
            'fee_type' => $data['fee_type'] ?? 'transportation',
            'amount' => $data['amount'],
            'currency' => $data['currency'] ?? 'IDR',
            'due_date' => $data['due_date'] ?? now()->addDays(30)->toDateString(),
            'payment_status' => 'pending',
            'payment_method' => $data['payment_method'] ?? null,
            'description' => $data['description'] ?? null,
        ]);
    }

    public function markFeePaid(string $feeId, string $paymentMethod = null, string $transactionReference = null): TransportationFee
    {
        $fee = TransportationFee::find($feeId);
        if (! $fee) {
            throw new \Exception('Fee not found');
        }

        $fee->update([
            'payment_status' => 'paid',
            'paid_date' => now()->toDateString(),
            'payment_method' => $paymentMethod,
            'transaction_reference' => $transactionReference,
        ]);

        return $fee;
    }

    public function createIncident(array $data): TransportationIncident
    {
        return TransportationIncident::create([
            'driver_id' => $data['driver_id'] ?? null,
            'vehicle_id' => $data['vehicle_id'] ?? null,
            'registration_id' => $data['registration_id'] ?? null,
            'incident_date' => $data['incident_date'] ?? now()->toDateString(),
            'incident_time' => $data['incident_time'] ?? now()->toTimeString(),
            'incident_type' => $data['incident_type'],
            'description' => $data['description'] ?? null,
            'severity' => $data['severity'] ?? 'medium',
            'status' => 'reported',
            'action_taken' => $data['action_taken'] ?? null,
            'witnesses' => $data['witnesses'] ?? null,
            'reported_by' => $data['reported_by'] ?? null,
        ]);
    }

    public function updateIncident(string $id, array $data): TransportationIncident
    {
        $incident = TransportationIncident::find($id);
        if (! $incident) {
            throw new \Exception('Incident not found');
        }

        $incident->update($data);
        return $incident;
    }

    public function getStudentRegistrations(string $studentId): array
    {
        return TransportationRegistration::where('student_id', $studentId)
            ->with(['route', 'assignments.fees'])
            ->get()
            ->toArray();
    }

    public function getAvailableVehicles(): array
    {
        return TransportationVehicle::active()->get()->toArray();
    }

    public function getAvailableDrivers(): array
    {
        return TransportationDriver::active()->get()->toArray();
    }

    public function getRouteStatistics(string $routeId): array
    {
        $route = TransportationRoute::find($routeId);
        if (! $route) {
            throw new \Exception('Route not found');
        }

        $activeRegistrations = $route->registrations()->active()->count();
        $totalFees = $route->registrations()->with('fees')
            ->get()
            ->sum(function ($registration) {
                return $registration->fees->sum('amount');
            });

        $paidFees = TransportationFee::whereHas('registration', function ($query) use ($routeId) {
            return $query->where('route_id', $routeId);
        })
            ->where('payment_status', 'paid')
            ->sum('amount');

        return [
            'route' => $route,
            'active_registrations' => $activeRegistrations,
            'total_fees' => $totalFees,
            'paid_fees' => $paidFees,
            'outstanding_fees' => $totalFees - $paidFees,
        ];
    }
}
