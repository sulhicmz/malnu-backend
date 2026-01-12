<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Transportation\TransportVehicle;
use App\Models\Transportation\TransportDriver;
use App\Models\Transportation\TransportRoute;
use App\Models\Transportation\TransportStop;
use App\Models\Transportation\TransportAssignment;
use App\Models\Transportation\TransportSchedule;
use App\Models\Transportation\TransportAttendance;
use App\Models\Transportation\TransportIncident;
use App\Models\Transportation\TransportTracking;
use App\Models\Student;
use App\Services\NotificationService;
use Carbon\Carbon;
use Hyperf\DbConnection\Db;
use Exception;

class TransportationService
{
    private NotificationService $notificationService;

    public function __construct(NotificationService $notificationService)
    {
        $this->notificationService = $notificationService;
    }

    public function createVehicle(array $data): TransportVehicle
    {
        return TransportVehicle::create($data);
    }

    public function updateVehicle(string $id, array $data): bool
    {
        $vehicle = TransportVehicle::find($id);
        if (!$vehicle) {
            return false;
        }
        return $vehicle->update($data);
    }

    public function deleteVehicle(string $id): bool
    {
        $vehicle = TransportVehicle::find($id);
        if (!$vehicle) {
            return false;
        }
        return $vehicle->delete();
    }

    public function getVehicle(string $id): ?TransportVehicle
    {
        return TransportVehicle::with(['latestTracking', 'driver'])->find($id);
    }

    public function getActiveVehicles(array $filters = [])
    {
        $query = TransportVehicle::active();

        if (isset($filters['vehicle_type'])) {
            $query->byType($filters['vehicle_type']);
        }

        return $query->get();
    }

    public function createDriver(array $data): TransportDriver
    {
        return TransportDriver::create($data);
    }

    public function updateDriver(string $id, array $data): bool
    {
        $driver = TransportDriver::find($id);
        if (!$driver) {
            return false;
        }
        return $driver->update($data);
    }

    public function deleteDriver(string $id): bool
    {
        $driver = TransportDriver::find($id);
        if (!$driver) {
            return false;
        }
        return $driver->delete();
    }

    public function getDriver(string $id): ?TransportDriver
    {
        return TransportDriver::find($id);
    }

    public function getActiveDrivers()
    {
        return TransportDriver::active()->get();
    }

    public function getDriversWithExpiringLicenses($days = 60)
    {
        return TransportDriver::expiringLicenses($days)->get();
    }

    public function createRoute(array $data): TransportRoute
    {
        $route = Db::transaction(function () use ($data) {
            $route = TransportRoute::create([
                'route_name' => $data['route_name'],
                'route_number' => $data['route_number'],
                'description' => $data['description'] ?? null,
                'route_type' => $data['route_type'] ?? 'regular',
                'status' => 'active',
                'start_time' => $data['start_time'],
                'end_time' => $data['end_time'],
                'total_distance' => $data['total_distance'] ?? 0,
                'estimated_duration' => $data['estimated_duration'] ?? 0,
                'created_by' => $data['created_by'] ?? null,
            ]);

            if (isset($data['stops']) && is_array($data['stops'])) {
                foreach ($data['stops'] as $index => $stop) {
                    TransportStop::create([
                        'route_id' => $route->id,
                        'stop_name' => $stop['stop_name'],
                        'description' => $stop['description'] ?? null,
                        'address' => $stop['address'] ?? null,
                        'latitude' => $stop['latitude'],
                        'longitude' => $stop['longitude'],
                        'stop_order' => $index + 1,
                        'arrival_time' => $stop['arrival_time'] ?? null,
                        'departure_time' => $stop['departure_time'] ?? null,
                        'pickup_point' => $stop['pickup_point'] ?? null,
                        'is_morning' => $stop['is_morning'] ?? true,
                        'is_afternoon' => $stop['is_afternoon'] ?? true,
                        'created_by' => $data['created_by'] ?? null,
                    ]);
                }
            }

            $route->update(['total_stops' => count($data['stops'] ?? [])]);

            return $route;
        });

        return $route->load('stops');
    }

    public function updateRoute(string $id, array $data): bool
    {
        $route = TransportRoute::find($id);
        if (!$route) {
            return false;
        }
        return $route->update($data);
    }

    public function deleteRoute(string $id): bool
    {
        $route = TransportRoute::find($id);
        if (!$route) {
            return false;
        }
        return $route->delete();
    }

    public function getRoute(string $id): ?TransportRoute
    {
        return TransportRoute::with(['stops', 'assignments', 'schedules'])->find($id);
    }

    public function getActiveRoutes()
    {
        return TransportRoute::active()->with(['stops', 'vehicle', 'driver'])->get();
    }

    public function createStop(array $data): TransportStop
    {
        return TransportStop::create($data);
    }

    public function updateStop(string $id, array $data): bool
    {
        $stop = TransportStop::find($id);
        if (!$stop) {
            return false;
        }
        return $stop->update($data);
    }

    public function deleteStop(string $id): bool
    {
        $stop = TransportStop::find($id);
        if (!$stop) {
            return false;
        }
        return $stop->delete();
    }

    public function assignStudent(array $data): TransportAssignment
    {
        return Db::transaction(function () use ($data) {
            return TransportAssignment::create([
                'student_id' => $data['student_id'],
                'route_id' => $data['route_id'],
                'stop_id' => $data['stop_id'],
                'vehicle_id' => $data['vehicle_id'] ?? null,
                'driver_id' => $data['driver_id'] ?? null,
                'effective_date' => $data['effective_date'] ?? now()->toDateString(),
                'end_date' => $data['end_date'] ?? null,
                'status' => 'active',
                'session_type' => $data['session_type'] ?? 'both',
                'fee_status' => $data['fee_status'] ?? 'pending',
                'monthly_fee' => $data['monthly_fee'] ?? 0,
                'additional_info' => $data['additional_info'] ?? null,
                'created_by' => $data['created_by'] ?? null,
            ]);
        });
    }

    public function updateAssignment(string $id, array $data): bool
    {
        $assignment = TransportAssignment::find($id);
        if (!$assignment) {
            return false;
        }
        return $assignment->update($data);
    }

    public function cancelAssignment(string $id): bool
    {
        $assignment = TransportAssignment::find($id);
        if (!$assignment) {
            return false;
        }
        $assignment->update(['status' => 'cancelled', 'end_date' => now()->toDateString()]);
        return true;
    }

    public function getAssignment(string $id): ?TransportAssignment
    {
        return TransportAssignment::with(['student', 'route', 'stop', 'vehicle', 'driver'])->find($id);
    }

    public function getStudentAssignments(string $studentId)
    {
        return TransportAssignment::with(['route', 'stop', 'vehicle', 'driver'])
            ->byStudent($studentId)
            ->active()
            ->get();
    }

    public function recordAttendance(array $data): TransportAttendance
    {
        $attendance = TransportAttendance::create($data);

        if ($attendance->boarding_status === 'missed') {
            $this->notifyParentsForMissedStudent($attendance);
        }

        return $attendance;
    }

    public function updateAttendance(string $id, array $data): bool
    {
        $attendance = TransportAttendance::find($id);
        if (!$attendance) {
            return false;
        }
        return $attendance->update($data);
    }

    public function getAttendance(string $id): ?TransportAttendance
    {
        return TransportAttendance::with(['assignment', 'student', 'boardingStop', 'alightingStop'])->find($id);
    }

    public function getTodayAttendance(array $filters = [])
    {
        $query = TransportAttendance::today()->with(['assignment.student', 'boardingStop', 'alightingStop']);

        if (isset($filters['session_type'])) {
            $query->where('session_type', $filters['session_type']);
        }

        if (isset($filters['boarding_status'])) {
            $query->where('boarding_status', $filters['boarding_status']);
        }

        return $query->get();
    }

    public function createSchedule(array $data): TransportSchedule
    {
        return TransportSchedule::create($data);
    }

    public function updateSchedule(string $id, array $data): bool
    {
        $schedule = TransportSchedule::find($id);
        if (!$schedule) {
            return false;
        }
        return $schedule->update($data);
    }

    public function deleteSchedule(string $id): bool
    {
        $schedule = TransportSchedule::find($id);
        if (!$schedule) {
            return false;
        }
        return $schedule->delete();
    }

    public function getTodaySchedules()
    {
        return TransportSchedule::today()->active()->with(['route', 'vehicle', 'driver'])->get();
    }

    public function reportIncident(array $data): TransportIncident
    {
        $incident = TransportIncident::create($data);

        if ($incident->severity === 'critical' || $incident->severity === 'major') {
            $this->notifyParentsForIncident($incident);
        }

        return $incident;
    }

    public function updateIncident(string $id, array $data): bool
    {
        $incident = TransportIncident::find($id);
        if (!$incident) {
            return false;
        }
        return $incident->update($data);
    }

    public function resolveIncident(string $id, array $data): bool
    {
        $incident = TransportIncident::find($id);
        if (!$incident) {
            return false;
        }
        return $incident->update([
            ...$data,
            'status' => 'resolved',
            'resolved_by' => $data['resolved_by'],
            'resolved_at' => now(),
        ]);
    }

    public function getIncident(string $id): ?TransportIncident
    {
        return TransportIncident::with(['vehicle', 'driver', 'route', 'reportedBy', 'resolvedBy'])->find($id);
    }

    public function getOpenIncidents()
    {
        return TransportIncident::open()->with(['vehicle', 'driver', 'route'])->get();
    }

    public function recordLocation(array $data): TransportTracking
    {
        $tracking = TransportTracking::create([
            'vehicle_id' => $data['vehicle_id'],
            'route_id' => $data['route_id'] ?? null,
            'driver_id' => $data['driver_id'] ?? null,
            'latitude' => $data['latitude'],
            'longitude' => $data['longitude'],
            'speed' => $data['speed'] ?? 0,
            'heading' => $data['heading'] ?? null,
            'altitude' => $data['altitude'] ?? null,
            'status' => $data['status'] ?? ($data['speed'] > 0 ? 'moving' : 'stopped'),
            'ignition_on' => $data['ignition_on'] ?? 1,
            'odometer' => $data['odometer'] ?? 0,
            'additional_data' => $data['additional_data'] ?? null,
            'recorded_at' => $data['recorded_at'] ?? now(),
        ]);

        $vehicle = TransportVehicle::find($data['vehicle_id']);
        if ($vehicle) {
            $vehicle->update([
                'current_location' => $data['current_location'] ?? null,
                'latitude' => $data['latitude'],
                'longitude' => $data['longitude'],
                'last_odometer' => $data['odometer'] ?? $vehicle->last_odometer,
            ]);
        }

        return $tracking;
    }

    public function getVehicleLocation(string $vehicleId): ?TransportTracking
    {
        return TransportTracking::byVehicle($vehicleId)
            ->recent(10)
            ->latest('recorded_at')
            ->first();
    }

    public function getVehiclesWithRecentLocations($minutes = 10)
    {
        return TransportVehicle::active()
            ->with(['latestTracking', 'driver'])
            ->whereHas('tracking', function ($query) use ($minutes) {
                $query->recent($minutes);
            })
            ->get();
    }

    public function getNearbyStops(float $latitude, float $longitude, float $radiusKm = 1)
    {
        return TransportStop::selectRaw("
            *,
            (
                6371 * ACOS(
                    COS(RADIANS(?)) * COS(RADIANS(latitude)) *
                    COS(RADIANS(longitude) - RADIANS(?)) +
                    SIN(RADIANS(?)) * SIN(RADIANS(latitude))
                )
            ) AS distance
        ", [$latitude, $longitude, $latitude])
            ->having('distance', '<=', $radiusKm)
            ->orderBy('distance')
            ->with('route')
            ->get();
    }

    public function calculateRouteDistance(array $stops): float
    {
        $totalDistance = 0;

        for ($i = 0; $i < count($stops) - 1; $i++) {
            $from = $stops[$i];
            $to = $stops[$i + 1];
            $totalDistance += $this->haversineDistance(
                $from['latitude'],
                $from['longitude'],
                $to['latitude'],
                $to['longitude']
            );
        }

        return round($totalDistance, 2);
    }

    private function haversineDistance(float $lat1, float $lon1, float $lat2, float $lon2): float
    {
        $earthRadius = 6371;

        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);

        $a = sin($dLat / 2) * sin($dLat / 2) +
            cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
            sin($dLon / 2) * sin($dLon / 2);

        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        return $earthRadius * $c;
    }

    private function notifyParentsForMissedStudent(TransportAttendance $attendance): void
    {
        $student = $attendance->student;

        if ($student) {
            $this->notificationService->sendNotification([
                'title' => 'Transportation Alert',
                'message' => "Your child {$student->name} missed the bus today",
                'type' => 'transportation',
                'priority' => 'high',
            ], [$student->user_id]);
        }
    }

    private function notifyParentsForIncident(TransportIncident $incident): void
    {
        if (!empty($incident->student_ids)) {
            $students = Student::whereIn('id', $incident->student_ids)->get();

            foreach ($students as $student) {
                $this->notificationService->sendNotification([
                    'title' => 'Transportation Emergency',
                    'message' => "Transportation incident on route: {$incident->description}",
                    'type' => 'transportation',
                    'priority' => 'critical',
                ], [$student->user_id]);
            }
        }
    }

    public function getTransportationStats(): array
    {
        return [
            'total_vehicles' => TransportVehicle::active()->count(),
            'total_drivers' => TransportDriver::active()->count(),
            'total_routes' => TransportRoute::active()->count(),
            'total_assignments' => TransportAssignment::active()->count(),
            'vehicles_on_route' => TransportVehicle::active()->whereHas('tracking', function ($q) {
                $q->recent(10)->where('status', 'moving');
            })->count(),
            'students_today' => TransportAttendance::today()->boarded()->count(),
            'missed_today' => TransportAttendance::today()->missed()->count(),
            'open_incidents' => TransportIncident::open()->count(),
        ];
    }
}
