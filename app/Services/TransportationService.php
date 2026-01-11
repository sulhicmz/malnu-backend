<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Transportation\TransportVehicle;
use App\Models\Transportation\TransportStop;
use App\Models\Transportation\TransportRoute;
use App\Models\Transportation\TransportRouteStop;
use App\Models\Transportation\TransportDriver;
use App\Models\Transportation\TransportAssignment;
use App\Models\Transportation\TransportSchedule;
use App\Models\Transportation\TransportAttendance;
use App\Models\Transportation\TransportTracking;
use App\Models\Transportation\TransportFee;
use App\Models\Transportation\TransportNotification;
use App\Models\User;
use Carbon\Carbon;
use Exception;

class TransportationService
{
    public function createVehicle(array $data): TransportVehicle
    {
        return TransportVehicle::create($data);
    }

    public function getVehicle(string $id): ?TransportVehicle
    {
        return TransportVehicle::find($id);
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

    public function getAllVehicles(array $filters = []): \Hyperf\Database\Model\Collection
    {
        $query = TransportVehicle::query();
        
        if (isset($filters['status'])) {
            $query->where('status', $filters['status']);
        }
        if (isset($filters['type'])) {
            $query->where('type', $filters['type']);
        }
        
        return $query->get();
    }

    public function createStop(array $data): TransportStop
    {
        return TransportStop::create($data);
    }

    public function getStop(string $id): ?TransportStop
    {
        return TransportStop::find($id);
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

    public function getAllStops(array $filters = []): \Hyperf\Database\Model\Collection
    {
        $query = TransportStop::query();
        
        if (isset($filters['is_active'])) {
            $query->where('is_active', $filters['is_active']);
        }
        
        return $query->get();
    }

    public function createRoute(array $data): TransportRoute
    {
        $route = TransportRoute::create($data);
        
        if (isset($data['stops']) && is_array($data['stops'])) {
            foreach ($data['stops'] as $index => $stopData) {
                TransportRouteStop::create([
                    'route_id' => $route->id,
                    'stop_id' => $stopData['stop_id'],
                    'sequence_order' => $stopData['sequence_order'] ?? $index + 1,
                    'pickup_time' => $stopData['pickup_time'] ?? null,
                    'dropoff_time' => $stopData['dropoff_time'] ?? null,
                    'distance_from_start' => $stopData['distance_from_start'] ?? null,
                    'estimated_duration' => $stopData['estimated_duration'] ?? null,
                ]);
            }
        }
        
        return $route;
    }

    public function getRoute(string $id): ?TransportRoute
    {
        return TransportRoute::with(['routeStops.stop', 'assignments.student', 'schedules.driver', 'schedules.vehicle'])->find($id);
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

    public function getAllRoutes(array $filters = []): \Hyperf\Database\Model\Collection
    {
        $query = TransportRoute::query();
        
        if (isset($filters['status'])) {
            $query->where('status', $filters['status']);
        }
        
        return $query->with(['routeStops.stop'])->get();
    }

    public function addStopToRoute(string $routeId, array $stopData): TransportRouteStop
    {
        return TransportRouteStop::create([
            'route_id' => $routeId,
            'stop_id' => $stopData['stop_id'],
            'sequence_order' => $stopData['sequence_order'],
            'pickup_time' => $stopData['pickup_time'] ?? null,
            'dropoff_time' => $stopData['dropoff_time'] ?? null,
            'distance_from_start' => $stopData['distance_from_start'] ?? null,
            'estimated_duration' => $stopData['estimated_duration'] ?? null,
        ]);
    }

    public function removeStopFromRoute(string $routeId, string $stopId): bool
    {
        return TransportRouteStop::where('route_id', $routeId)
            ->where('stop_id', $stopId)
            ->delete() > 0;
    }

    public function createDriver(array $data): TransportDriver
    {
        return TransportDriver::create($data);
    }

    public function getDriver(string $id): ?TransportDriver
    {
        return TransportDriver::with(['user', 'schedules.route'])->find($id);
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

    public function getAllDrivers(array $filters = []): \Hyperf\Database\Model\Collection
    {
        $query = TransportDriver::query();
        
        if (isset($filters['status'])) {
            $query->where('status', $filters['status']);
        }
        
        return $query->with(['user'])->get();
    }

    public function createSchedule(array $data): TransportSchedule
    {
        return TransportSchedule::create($data);
    }

    public function getSchedule(string $id): ?TransportSchedule
    {
        return TransportSchedule::with(['route', 'vehicle', 'driver'])->find($id);
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

    public function getAllSchedules(array $filters = []): \Hyperf\Database\Model\Collection
    {
        $query = TransportSchedule::query();
        
        if (isset($filters['status'])) {
            $query->where('status', $filters['status']);
        }
        if (isset($filters['route_id'])) {
            $query->where('route_id', $filters['route_id']);
        }
        if (isset($filters['driver_id'])) {
            $query->where('driver_id', $filters['driver_id']);
        }
        
        return $query->with(['route', 'vehicle', 'driver'])->get();
    }

    public function createAssignment(array $data): TransportAssignment
    {
        $existing = TransportAssignment::where('student_id', $data['student_id'])
            ->where('status', 'active')
            ->first();
        
        if ($existing) {
            throw new Exception('Student already has an active transportation assignment');
        }
        
        return TransportAssignment::create($data);
    }

    public function getAssignment(string $id): ?TransportAssignment
    {
        return TransportAssignment::with(['route', 'student', 'pickupStop', 'dropoffStop'])->find($id);
    }

    public function updateAssignment(string $id, array $data): bool
    {
        $assignment = TransportAssignment::find($id);
        if (!$assignment) {
            return false;
        }
        return $assignment->update($data);
    }

    public function deleteAssignment(string $id): bool
    {
        $assignment = TransportAssignment::find($id);
        if (!$assignment) {
            return false;
        }
        return $assignment->delete();
    }

    public function getStudentAssignments(string $studentId, array $filters = []): \Hyperf\Database\Model\Collection
    {
        $query = TransportAssignment::where('student_id', $studentId);
        
        if (isset($filters['status'])) {
            $query->where('status', $filters['status']);
        }
        
        return $query->with(['route', 'pickupStop', 'dropoffStop'])->get();
    }

    public function getRouteAssignments(string $routeId, array $filters = []): \Hyperf\Database\Model\Collection
    {
        $query = TransportAssignment::where('route_id', $routeId);
        
        if (isset($filters['status'])) {
            $query->where('status', $filters['status']);
        }
        
        return $query->with(['student', 'pickupStop', 'dropoffStop'])->get();
    }

    public function recordAttendance(array $data): TransportAttendance
    {
        $existing = TransportAttendance::where('assignment_id', $data['assignment_id'])
            ->where('attendance_date', $data['attendance_date'])
            ->where('trip_type', $data['trip_type'])
            ->first();
        
        if ($existing) {
            $existing->update($data);
            return $existing;
        }
        
        return TransportAttendance::create($data);
    }

    public function getAttendance(string $id): ?TransportAttendance
    {
        return TransportAttendance::with(['assignment', 'student', 'route'])->find($id);
    }

    public function getStudentAttendance(string $studentId, string $startDate, string $endDate): \Hyperf\Database\Model\Collection
    {
        return TransportAttendance::where('student_id', $studentId)
            ->whereBetween('attendance_date', [$startDate, $endDate])
            ->with(['assignment', 'route'])
            ->orderBy('attendance_date')
            ->orderBy('trip_type')
            ->get();
    }

    public function getRouteAttendance(string $routeId, string $date): \Hyperf\Database\Model\Collection
    {
        return TransportAttendance::where('route_id', $routeId)
            ->where('attendance_date', $date)
            ->with(['student', 'assignment'])
            ->orderBy('trip_type')
            ->get();
    }

    public function recordVehicleLocation(array $data): TransportTracking
    {
        return TransportTracking::create(array_merge($data, [
            'recorded_at' => Carbon::now(),
        ]));
    }

    public function getVehicleLocation(string $vehicleId, ?string $scheduleId = null): ?TransportTracking
    {
        $query = TransportTracking::where('vehicle_id', $vehicleId);
        
        if ($scheduleId) {
            $query->where('schedule_id', $scheduleId);
        }
        
        return $query->orderBy('recorded_at', 'desc')->first();
    }

    public function getVehicleHistory(string $vehicleId, string $startDate, string $endDate): \Hyperf\Database\Model\Collection
    {
        return TransportTracking::where('vehicle_id', $vehicleId)
            ->whereBetween('recorded_at', [$startDate, $endDate])
            ->orderBy('recorded_at')
            ->get();
    }

    public function createFee(array $data): TransportFee
    {
        return TransportFee::create($data);
    }

    public function getFee(string $id): ?TransportFee
    {
        return TransportFee::with(['student', 'route', 'assignment'])->find($id);
    }

    public function updateFee(string $id, array $data): bool
    {
        $fee = TransportFee::find($id);
        if (!$fee) {
            return false;
        }
        return $fee->update($data);
    }

    public function markFeePaid(string $id, array $paymentData): bool
    {
        $fee = TransportFee::find($id);
        if (!$fee) {
            return false;
        }
        return $fee->update([
            'status' => 'paid',
            'paid_date' => Carbon::now(),
            'payment_method' => $paymentData['payment_method'] ?? null,
            'transaction_reference' => $paymentData['transaction_reference'] ?? null,
        ]);
    }

    public function getStudentFees(string $studentId, array $filters = []): \Hyperf\Database\Model\Collection
    {
        $query = TransportFee::where('student_id', $studentId);
        
        if (isset($filters['status'])) {
            $query->where('status', $filters['status']);
        }
        if (isset($filters['fee_type'])) {
            $query->where('fee_type', $filters['fee_type']);
        }
        
        return $query->with(['route', 'assignment'])->orderBy('due_date')->get();
    }

    public function getPendingFees(?string $studentId = null): \Hyperf\Database\Model\Collection
    {
        $query = TransportFee::where('status', 'pending');
        
        if ($studentId) {
            $query->where('student_id', $studentId);
        }
        
        return $query->where('due_date', '<=', Carbon::now()->addDays(7)->toDateString())
            ->with(['student', 'route'])
            ->orderBy('due_date')
            ->get();
    }

    public function createNotification(array $data): TransportNotification
    {
        return TransportNotification::create($data);
    }

    public function getNotification(string $id): ?TransportNotification
    {
        return TransportNotification::with(['route', 'vehicle', 'student'])->find($id);
    }

    public function sendNotification(string $id): bool
    {
        $notification = TransportNotification::find($id);
        if (!$notification) {
            return false;
        }
        
        $notification->update([
            'is_sent' => true,
            'sent_at' => Carbon::now(),
        ]);
        
        return true;
    }

    public function createBusDelayNotification(array $data): TransportNotification
    {
        return TransportNotification::create([
            'route_id' => $data['route_id'],
            'vehicle_id' => $data['vehicle_id'] ?? null,
            'notification_type' => 'bus_delay',
            'title' => $data['title'] ?? 'Bus Delay Alert',
            'message' => $data['message'],
            'priority' => $data['priority'] ?? 'high',
            'recipient_ids' => $data['recipient_ids'] ?? null,
        ]);
    }

    public function createEmergencyNotification(array $data): TransportNotification
    {
        return TransportNotification::create([
            'route_id' => $data['route_id'] ?? null,
            'vehicle_id' => $data['vehicle_id'] ?? null,
            'notification_type' => 'emergency',
            'title' => $data['title'] ?? 'Emergency Alert',
            'message' => $data['message'],
            'priority' => 'urgent',
            'recipient_ids' => $data['recipient_ids'] ?? null,
        ]);
    }

    public function getPendingNotifications(): \Hyperf\Database\Model\Collection
    {
        return TransportNotification::where('is_sent', false)
            ->with(['route', 'vehicle'])
            ->orderBy('priority', 'desc')
            ->orderBy('created_at')
            ->get();
    }

    public function getVehicleOccupancy(string $routeId): array
    {
        $route = TransportRoute::find($routeId);
        if (!$route) {
            return [];
        }
        
        $activeAssignments = TransportAssignment::where('route_id', $routeId)
            ->where('status', 'active')
            ->count();
        
        return [
            'capacity' => $route->capacity,
            'assigned_students' => $activeAssignments,
            'available_seats' => $route->capacity - $activeAssignments,
            'occupancy_rate' => $route->capacity > 0 ? round(($activeAssignments / $route->capacity) * 100, 2) : 0,
        ];
    }

    public function getRouteAnalytics(string $routeId, string $startDate, string $endDate): array
    {
        $attendance = TransportAttendance::where('route_id', $routeId)
            ->whereBetween('attendance_date', [$startDate, $endDate])
            ->get();
        
        $totalTrips = $attendance->count();
        $presentPickups = $attendance->where('pickup_status', 'present')->count();
        $presentDropoffs = $attendance->where('dropoff_status', 'present')->count();
        
        return [
            'total_trips' => $totalTrips,
            'pickup_attendance_rate' => $totalTrips > 0 ? round(($presentPickups / $totalTrips) * 100, 2) : 0,
            'dropoff_attendance_rate' => $totalTrips > 0 ? round(($presentDropoffs / $totalTrips) * 100, 2) : 0,
            'overall_attendance_rate' => $totalTrips > 0 ? round((($presentPickups + $presentDropoffs) / ($totalTrips * 2)) * 100, 2) : 0,
            'late_pickups' => $attendance->where('pickup_status', 'late')->count(),
            'missed_dropoffs' => $attendance->where('dropoff_status', 'missed')->count(),
        ];
    }

    public function getTransportationReport(array $filters = []): array
    {
        $vehicleCount = TransportVehicle::where('status', 'active')->count();
        $routeCount = TransportRoute::where('status', 'active')->count();
        $driverCount = TransportDriver::where('status', 'active')->count();
        $activeAssignments = TransportAssignment::where('status', 'active')->count();
        $pendingFees = TransportFee::where('status', 'pending')->count();
        $pendingNotifications = TransportNotification::where('is_sent', false)->count();
        
        return [
            'active_vehicles' => $vehicleCount,
            'active_routes' => $routeCount,
            'active_drivers' => $driverCount,
            'students_transportation' => $activeAssignments,
            'pending_fees' => $pendingFees,
            'pending_notifications' => $pendingNotifications,
        ];
    }
}
