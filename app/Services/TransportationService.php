<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Transport\TransportVehicle;
use App\Models\Transport\TransportDriver;
use App\Models\Transport\TransportStop;
use App\Models\Transport\TransportRoute;
use App\Models\Transport\TransportRouteStop;
use App\Models\Transport\TransportSchedule;
use App\Models\Transport\TransportAssignment;
use App\Models\Transport\TransportAttendance;
use App\Models\Transport\TransportTracking;
use App\Models\Transport\TransportIncident;
use App\Models\User;

class TransportationService
{
    public function createVehicle(array $data): TransportVehicle
    {
        return TransportVehicle::create($data);
    }

    public function getVehicles(array $filters = [])
    {
        $query = TransportVehicle::query();

        if (isset($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (isset($filters['is_active'])) {
            $query->where('is_active', $filters['is_active']);
        }

        return $query->get();
    }

    public function getVehicle(string $id): ?TransportVehicle
    {
        return TransportVehicle::find($id);
    }

    public function updateVehicle(string $id, array $data): ?TransportVehicle
    {
        $vehicle = $this->getVehicle($id);
        if ($vehicle) {
            $vehicle->update($data);
        }

        return $vehicle;
    }

    public function deleteVehicle(string $id): bool
    {
        $vehicle = $this->getVehicle($id);
        if ($vehicle) {
            return $vehicle->delete();
        }

        return false;
    }

    public function createDriver(array $data): TransportDriver
    {
        return TransportDriver::create($data);
    }

    public function getDrivers(array $filters = [])
    {
        $query = TransportDriver::query();

        if (isset($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (isset($filters['is_active'])) {
            $query->where('is_active', $filters['is_active']);
        }

        return $query->get();
    }

    public function getDriver(string $id): ?TransportDriver
    {
        return TransportDriver::find($id);
    }

    public function updateDriver(string $id, array $data): ?TransportDriver
    {
        $driver = $this->getDriver($id);
        if ($driver) {
            $driver->update($data);
        }

        return $driver;
    }

    public function createStop(array $data): TransportStop
    {
        return TransportStop::create($data);
    }

    public function getStops(array $filters = [])
    {
        $query = TransportStop::query();

        if (isset($filters['type'])) {
            $query->where('type', $filters['type']);
        }

        if (isset($filters['is_active'])) {
            $query->where('is_active', $filters['is_active']);
        }

        return $query->get();
    }

    public function getNearbyStops(float $latitude, float $longitude, float $radiusKm = 5)
    {
        $stops = TransportStop::whereNotNull('latitude')
            ->whereNotNull('longitude')
            ->get();

        return $stops->filter(function ($stop) use ($latitude, $longitude, $radiusKm) {
            return $stop->distanceTo($latitude, $longitude) <= $radiusKm;
        });
    }

    public function getStop(string $id): ?TransportStop
    {
        return TransportStop::find($id);
    }

    public function updateStop(string $id, array $data): ?TransportStop
    {
        $stop = $this->getStop($id);
        if ($stop) {
            $stop->update($data);
        }

        return $stop;
    }

    public function deleteStop(string $id): bool
    {
        $stop = $this->getStop($id);
        if ($stop) {
            return $stop->delete();
        }

        return false;
    }

    public function createRoute(array $data): TransportRoute
    {
        return TransportRoute::create($data);
    }

    public function getRoutes(array $filters = [])
    {
        $query = TransportRoute::query();

        if (isset($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (isset($filters['is_active'])) {
            $query->where('is_active', $filters['is_active']);
        }

        return $query->with('stops')->get();
    }

    public function getRoute(string $id): ?TransportRoute
    {
        return TransportRoute::with('stops')->find($id);
    }

    public function updateRoute(string $id, array $data): ?TransportRoute
    {
        $route = $this->getRoute($id);
        if ($route) {
            $route->update($data);
        }

        return $route;
    }

    public function deleteRoute(string $id): bool
    {
        $route = $this->getRoute($id);
        if ($route) {
            return $route->delete();
        }

        return false;
    }

    public function addStopToRoute(string $routeId, string $stopId, int $order, array $data = []): TransportRouteStop
    {
        return TransportRouteStop::create(array_merge($data, [
            'route_id' => $routeId,
            'stop_id' => $stopId,
            'stop_order' => $order,
        ]));
    }

    public function removeStopFromRoute(string $routeId, string $stopId): bool
    {
        return TransportRouteStop::where('route_id', $routeId)
            ->where('stop_id', $stopId)
            ->delete();
    }

    public function createSchedule(array $data): TransportSchedule
    {
        return TransportSchedule::create($data);
    }

    public function getSchedules(array $filters = [])
    {
        $query = TransportSchedule::query();

        if (isset($filters['day_of_week'])) {
            $query->where('day_of_week', $filters['day_of_week']);
        }

        if (isset($filters['route_id'])) {
            $query->where('route_id', $filters['route_id']);
        }

        if (isset($filters['vehicle_id'])) {
            $query->where('vehicle_id', $filters['vehicle_id']);
        }

        if (isset($filters['driver_id'])) {
            $query->where('driver_id', $filters['driver_id']);
        }

        return $query->with(['route', 'vehicle', 'driver'])->get();
    }

    public function getSchedule(string $id): ?TransportSchedule
    {
        return TransportSchedule::with(['route', 'vehicle', 'driver'])->find($id);
    }

    public function updateSchedule(string $id, array $data): ?TransportSchedule
    {
        $schedule = $this->getSchedule($id);
        if ($schedule) {
            $schedule->update($data);
        }

        return $schedule;
    }

    public function deleteSchedule(string $id): bool
    {
        $schedule = $this->getSchedule($id);
        if ($schedule) {
            return $schedule->delete();
        }

        return false;
    }

    public function assignStudentToRoute(string $studentId, string $routeId, array $data = []): TransportAssignment
    {
        return TransportAssignment::create(array_merge($data, [
            'student_id' => $studentId,
            'route_id' => $routeId,
            'start_date' => date('Y-m-d'),
        ]));
    }

    public function getStudentAssignments(string $studentId)
    {
        return TransportAssignment::where('student_id', $studentId)
            ->with(['route', 'pickupStop', 'dropoffStop'])
            ->where('is_active', true)
            ->get();
    }

    public function getRouteAssignments(string $routeId)
    {
        return TransportAssignment::where('route_id', $routeId)
            ->with(['student', 'pickupStop', 'dropoffStop'])
            ->where('is_active', true)
            ->get();
    }

    public function updateAssignment(string $id, array $data): ?TransportAssignment
    {
        $assignment = TransportAssignment::find($id);
        if ($assignment) {
            $assignment->update($data);
        }

        return $assignment;
    }

    public function cancelAssignment(string $id): bool
    {
        $assignment = TransportAssignment::find($id);
        if ($assignment) {
            $assignment->update(['is_active' => false, 'end_date' => date('Y-m-d')]);
            return true;
        }

        return false;
    }

    public function recordAttendance(string $assignmentId, string $routeId, string $studentId, array $data): TransportAttendance
    {
        return TransportAttendance::create(array_merge($data, [
            'assignment_id' => $assignmentId,
            'route_id' => $routeId,
            'student_id' => $studentId,
            'attendance_date' => date('Y-m-d'),
        ]));
    }

    public function getTodayAttendance(string $routeId, ?string $date = null)
    {
        $date = $date ?? date('Y-m-d');

        return TransportAttendance::where('route_id', $routeId)
            ->where('attendance_date', $date)
            ->with(['student', 'assignment'])
            ->get();
    }

    public function getStudentAttendance(string $studentId, ?string $date = null)
    {
        $date = $date ?? date('Y-m-d');

        return TransportAttendance::where('student_id', $studentId)
            ->where('attendance_date', $date)
            ->with(['route'])
            ->first();
    }

    public function recordTracking(string $vehicleId, array $data): TransportTracking
    {
        return TransportTracking::create(array_merge($data, [
            'vehicle_id' => $vehicleId,
            'recorded_at' => now(),
        ]));
    }

    public function getVehicleTracking(string $vehicleId, int $limit = 100)
    {
        return TransportTracking::where('vehicle_id', $vehicleId)
            ->latest()
            ->limit($limit)
            ->get();
    }

    public function getActiveVehicleLocations()
    {
        return TransportTracking::activeVehicles()
            ->latest('recorded_at')
            ->get()
            ->unique('vehicle_id')
            ->load('vehicle');
    }

    public function reportIncident(array $data): TransportIncident
    {
        return TransportIncident::create(array_merge($data, [
            'incident_time' => now(),
            'status' => 'open',
        ]));
    }

    public function getIncidents(array $filters = [])
    {
        $query = TransportIncident::query();

        if (isset($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (isset($filters['severity'])) {
            $query->where('severity', $filters['severity']);
        }

        if (isset($filters['vehicle_id'])) {
            $query->where('vehicle_id', $filters['vehicle_id']);
        }

        if (isset($filters['route_id'])) {
            $query->where('route_id', $filters['route_id']);
        }

        return $query->with(['vehicle', 'route', 'driver'])
            ->orderBy('incident_time', 'desc')
            ->get();
    }

    public function getIncident(string $id): ?TransportIncident
    {
        return TransportIncident::with(['vehicle', 'route', 'driver'])->find($id);
    }

    public function resolveIncident(string $id, string $resolution): ?TransportIncident
    {
        $incident = $this->getIncident($id);
        if ($incident) {
            $incident->update([
                'status' => 'resolved',
                'resolution' => $resolution,
                'resolved_at' => now(),
            ]);
        }

        return $incident;
    }

    public function getStatistics(): array
    {
        return [
            'total_vehicles' => TransportVehicle::count(),
            'active_vehicles' => TransportVehicle::where('is_active', true)->where('status', 'available')->count(),
            'total_drivers' => TransportDriver::count(),
            'active_drivers' => TransportDriver::where('is_active', true)->where('status', 'available')->count(),
            'total_routes' => TransportRoute::count(),
            'active_routes' => TransportRoute::where('is_active', true)->where('status', 'active')->count(),
            'total_assignments' => TransportAssignment::where('is_active', true)->count(),
            'today_attendance' => TransportAttendance::where('attendance_date', date('Y-m-d'))->count(),
            'open_incidents' => TransportIncident::where('status', 'open')->count(),
            'active_tracking' => TransportTracking::where('recorded_at', '>=', now()->subMinutes(5))->distinct('vehicle_id')->count(),
        ];
    }
}