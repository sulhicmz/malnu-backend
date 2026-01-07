<?php

declare(strict_types=1);

namespace App\Http\Controllers\Transportation;

use App\Http\Controllers\AbstractController;
use App\Services\TransportationService;
use Hyperf\HttpServer\Annotation\Controller;
use Hyperf\HttpServer\Annotation\GetMapping;
use Hyperf\HttpServer\Annotation\PostMapping;
use Hyperf\HttpServer\Annotation\PutMapping;
use Hyperf\HttpServer\Annotation\DeleteMapping;
use Hyperf\HttpServer\Annotation\Middleware;
use App\Http\Middleware\JWTMiddleware;
use Psr\Http\Message\ResponseInterface;

/**
 * @Controller(prefix="api/transportation")
 * @Middleware(JWTMiddleware::class)
 */
class TransportationController extends AbstractController
{
    private TransportationService $transportationService;

    public function __construct(TransportationService $transportationService)
    {
        $this->transportationService = $transportationService;
    }

    private function errorResponse(string $message, int $status = 400): ResponseInterface
    {
        return $this->response->json([
            'success' => false,
            'message' => $message
        ])->withStatus($status);
    }

    private function successResponse($data, string $message = 'Success', int $status = 200): ResponseInterface
    {
        return $this->response->json([
            'success' => true,
            'data' => $data,
            'message' => $message
        ])->withStatus($status);
    }

    /**
     * @PostMapping(path="vehicles")
     */
    public function createVehicle(): ResponseInterface
    {
        $data = $this->request->all();
        
        if (empty($data['vehicle_number']) || empty($data['license_plate']) || empty($data['capacity'])) {
            return $this->errorResponse('Vehicle number, license plate, and capacity are required');
        }

        try {
            $vehicle = $this->transportationService->createVehicle($data);
            return $this->successResponse($vehicle, 'Vehicle created successfully');
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to create vehicle: ' . $e->getMessage(), 500);
        }
    }

    /**
     * @GetMapping(path="vehicles")
     */
    public function getAllVehicles(): ResponseInterface
    {
        try {
            $filters = $this->request->all();
            $vehicles = $this->transportationService->getAllVehicles($filters);
            return $this->successResponse($vehicles);
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to retrieve vehicles: ' . $e->getMessage(), 500);
        }
    }

    /**
     * @GetMapping(path="vehicles/{id}")
     */
    public function getVehicle(string $id): ResponseInterface
    {
        try {
            $vehicle = $this->transportationService->getVehicle($id);
            
            if (!$vehicle) {
                return $this->errorResponse('Vehicle not found', 404);
            }
            
            return $this->successResponse($vehicle);
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to retrieve vehicle: ' . $e->getMessage(), 500);
        }
    }

    /**
     * @PutMapping(path="vehicles/{id}")
     */
    public function updateVehicle(string $id): ResponseInterface
    {
        $data = $this->request->all();
        
        try {
            $result = $this->transportationService->updateVehicle($id, $data);
            
            if (!$result) {
                return $this->errorResponse('Vehicle not found', 404);
            }
            
            $vehicle = $this->transportationService->getVehicle($id);
            return $this->successResponse($vehicle, 'Vehicle updated successfully');
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to update vehicle: ' . $e->getMessage(), 500);
        }
    }

    /**
     * @DeleteMapping(path="vehicles/{id}")
     */
    public function deleteVehicle(string $id): ResponseInterface
    {
        try {
            $result = $this->transportationService->deleteVehicle($id);
            
            if (!$result) {
                return $this->errorResponse('Vehicle not found', 404);
            }
            
            return $this->successResponse(null, 'Vehicle deleted successfully');
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to delete vehicle: ' . $e->getMessage(), 500);
        }
    }

    /**
     * @PostMapping(path="stops")
     */
    public function createStop(): ResponseInterface
    {
        $data = $this->request->all();
        
        if (empty($data['name']) || empty($data['address']) || empty($data['latitude']) || empty($data['longitude'])) {
            return $this->errorResponse('Name, address, latitude, and longitude are required');
        }

        try {
            $stop = $this->transportationService->createStop($data);
            return $this->successResponse($stop, 'Stop created successfully');
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to create stop: ' . $e->getMessage(), 500);
        }
    }

    /**
     * @GetMapping(path="stops")
     */
    public function getAllStops(): ResponseInterface
    {
        try {
            $filters = $this->request->all();
            $stops = $this->transportationService->getAllStops($filters);
            return $this->successResponse($stops);
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to retrieve stops: ' . $e->getMessage(), 500);
        }
    }

    /**
     * @GetMapping(path="stops/{id}")
     */
    public function getStop(string $id): ResponseInterface
    {
        try {
            $stop = $this->transportationService->getStop($id);
            
            if (!$stop) {
                return $this->errorResponse('Stop not found', 404);
            }
            
            return $this->successResponse($stop);
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to retrieve stop: ' . $e->getMessage(), 500);
        }
    }

    /**
     * @PutMapping(path="stops/{id}")
     */
    public function updateStop(string $id): ResponseInterface
    {
        $data = $this->request->all();
        
        try {
            $result = $this->transportationService->updateStop($id, $data);
            
            if (!$result) {
                return $this->errorResponse('Stop not found', 404);
            }
            
            $stop = $this->transportationService->getStop($id);
            return $this->successResponse($stop, 'Stop updated successfully');
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to update stop: ' . $e->getMessage(), 500);
        }
    }

    /**
     * @DeleteMapping(path="stops/{id}")
     */
    public function deleteStop(string $id): ResponseInterface
    {
        try {
            $result = $this->transportationService->deleteStop($id);
            
            if (!$result) {
                return $this->errorResponse('Stop not found', 404);
            }
            
            return $this->successResponse(null, 'Stop deleted successfully');
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to delete stop: ' . $e->getMessage(), 500);
        }
    }

    /**
     * @PostMapping(path="routes")
     */
    public function createRoute(): ResponseInterface
    {
        $data = $this->request->all();
        
        if (empty($data['route_number']) || empty($data['name']) || empty($data['start_location']) || empty($data['end_location'])) {
            return $this->errorResponse('Route number, name, start location, and end location are required');
        }

        try {
            $route = $this->transportationService->createRoute($data);
            return $this->successResponse($route, 'Route created successfully');
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to create route: ' . $e->getMessage(), 500);
        }
    }

    /**
     * @GetMapping(path="routes")
     */
    public function getAllRoutes(): ResponseInterface
    {
        try {
            $filters = $this->request->all();
            $routes = $this->transportationService->getAllRoutes($filters);
            return $this->successResponse($routes);
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to retrieve routes: ' . $e->getMessage(), 500);
        }
    }

    /**
     * @GetMapping(path="routes/{id}")
     */
    public function getRoute(string $id): ResponseInterface
    {
        try {
            $route = $this->transportationService->getRoute($id);
            
            if (!$route) {
                return $this->errorResponse('Route not found', 404);
            }
            
            return $this->successResponse($route);
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to retrieve route: ' . $e->getMessage(), 500);
        }
    }

    /**
     * @PutMapping(path="routes/{id}")
     */
    public function updateRoute(string $id): ResponseInterface
    {
        $data = $this->request->all();
        
        try {
            $result = $this->transportationService->updateRoute($id, $data);
            
            if (!$result) {
                return $this->errorResponse('Route not found', 404);
            }
            
            $route = $this->transportationService->getRoute($id);
            return $this->successResponse($route, 'Route updated successfully');
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to update route: ' . $e->getMessage(), 500);
        }
    }

    /**
     * @DeleteMapping(path="routes/{id}")
     */
    public function deleteRoute(string $id): ResponseInterface
    {
        try {
            $result = $this->transportationService->deleteRoute($id);
            
            if (!$result) {
                return $this->errorResponse('Route not found', 404);
            }
            
            return $this->successResponse(null, 'Route deleted successfully');
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to delete route: ' . $e->getMessage(), 500);
        }
    }

    /**
     * @PostMapping(path="routes/{routeId}/stops")
     */
    public function addStopToRoute(string $routeId): ResponseInterface
    {
        $data = $this->request->all();
        
        if (empty($data['stop_id']) || empty($data['sequence_order'])) {
            return $this->errorResponse('Stop ID and sequence order are required');
        }

        try {
            $routeStop = $this->transportationService->addStopToRoute($routeId, $data);
            return $this->successResponse($routeStop, 'Stop added to route successfully');
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to add stop to route: ' . $e->getMessage(), 500);
        }
    }

    /**
     * @DeleteMapping(path="routes/{routeId}/stops/{stopId}")
     */
    public function removeStopFromRoute(string $routeId, string $stopId): ResponseInterface
    {
        try {
            $result = $this->transportationService->removeStopFromRoute($routeId, $stopId);
            return $this->successResponse(null, 'Stop removed from route successfully');
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to remove stop from route: ' . $e->getMessage(), 500);
        }
    }

    /**
     * @PostMapping(path="drivers")
     */
    public function createDriver(): ResponseInterface
    {
        $data = $this->request->all();
        
        if (empty($data['name']) || empty($data['phone']) || empty($data['license_number']) || empty($data['license_expiry'])) {
            return $this->errorResponse('Name, phone, license number, and license expiry are required');
        }

        try {
            $driver = $this->transportationService->createDriver($data);
            return $this->successResponse($driver, 'Driver created successfully');
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to create driver: ' . $e->getMessage(), 500);
        }
    }

    /**
     * @GetMapping(path="drivers")
     */
    public function getAllDrivers(): ResponseInterface
    {
        try {
            $filters = $this->request->all();
            $drivers = $this->transportationService->getAllDrivers($filters);
            return $this->successResponse($drivers);
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to retrieve drivers: ' . $e->getMessage(), 500);
        }
    }

    /**
     * @GetMapping(path="drivers/{id}")
     */
    public function getDriver(string $id): ResponseInterface
    {
        try {
            $driver = $this->transportationService->getDriver($id);
            
            if (!$driver) {
                return $this->errorResponse('Driver not found', 404);
            }
            
            return $this->successResponse($driver);
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to retrieve driver: ' . $e->getMessage(), 500);
        }
    }

    /**
     * @PutMapping(path="drivers/{id}")
     */
    public function updateDriver(string $id): ResponseInterface
    {
        $data = $this->request->all();
        
        try {
            $result = $this->transportationService->updateDriver($id, $data);
            
            if (!$result) {
                return $this->errorResponse('Driver not found', 404);
            }
            
            $driver = $this->transportationService->getDriver($id);
            return $this->successResponse($driver, 'Driver updated successfully');
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to update driver: ' . $e->getMessage(), 500);
        }
    }

    /**
     * @DeleteMapping(path="drivers/{id}")
     */
    public function deleteDriver(string $id): ResponseInterface
    {
        try {
            $result = $this->transportationService->deleteDriver($id);
            
            if (!$result) {
                return $this->errorResponse('Driver not found', 404);
            }
            
            return $this->successResponse(null, 'Driver deleted successfully');
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to delete driver: ' . $e->getMessage(), 500);
        }
    }

    /**
     * @PostMapping(path="schedules")
     */
    public function createSchedule(): ResponseInterface
    {
        $data = $this->request->all();
        
        if (empty($data['route_id']) || empty($data['vehicle_id']) || empty($data['driver_id']) || empty($data['effective_start_date'])) {
            return $this->errorResponse('Route ID, vehicle ID, driver ID, and effective start date are required');
        }

        try {
            $schedule = $this->transportationService->createSchedule($data);
            return $this->successResponse($schedule, 'Schedule created successfully');
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to create schedule: ' . $e->getMessage(), 500);
        }
    }

    /**
     * @GetMapping(path="schedules")
     */
    public function getAllSchedules(): ResponseInterface
    {
        try {
            $filters = $this->request->all();
            $schedules = $this->transportationService->getAllSchedules($filters);
            return $this->successResponse($schedules);
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to retrieve schedules: ' . $e->getMessage(), 500);
        }
    }

    /**
     * @GetMapping(path="schedules/{id}")
     */
    public function getSchedule(string $id): ResponseInterface
    {
        try {
            $schedule = $this->transportationService->getSchedule($id);
            
            if (!$schedule) {
                return $this->errorResponse('Schedule not found', 404);
            }
            
            return $this->successResponse($schedule);
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to retrieve schedule: ' . $e->getMessage(), 500);
        }
    }

    /**
     * @PutMapping(path="schedules/{id}")
     */
    public function updateSchedule(string $id): ResponseInterface
    {
        $data = $this->request->all();
        
        try {
            $result = $this->transportationService->updateSchedule($id, $data);
            
            if (!$result) {
                return $this->errorResponse('Schedule not found', 404);
            }
            
            $schedule = $this->transportationService->getSchedule($id);
            return $this->successResponse($schedule, 'Schedule updated successfully');
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to update schedule: ' . $e->getMessage(), 500);
        }
    }

    /**
     * @DeleteMapping(path="schedules/{id}")
     */
    public function deleteSchedule(string $id): ResponseInterface
    {
        try {
            $result = $this->transportationService->deleteSchedule($id);
            
            if (!$result) {
                return $this->errorResponse('Schedule not found', 404);
            }
            
            return $this->successResponse(null, 'Schedule deleted successfully');
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to delete schedule: ' . $e->getMessage(), 500);
        }
    }

    /**
     * @PostMapping(path="assignments")
     */
    public function createAssignment(): ResponseInterface
    {
        $data = $this->request->all();
        
        if (empty($data['route_id']) || empty($data['student_id']) || empty($data['start_date'])) {
            return $this->errorResponse('Route ID, student ID, and start date are required');
        }

        try {
            $assignment = $this->transportationService->createAssignment($data);
            return $this->successResponse($assignment, 'Assignment created successfully');
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to create assignment: ' . $e->getMessage(), 500);
        }
    }

    /**
     * @GetMapping(path="assignments/{id}")
     */
    public function getAssignment(string $id): ResponseInterface
    {
        try {
            $assignment = $this->transportationService->getAssignment($id);
            
            if (!$assignment) {
                return $this->errorResponse('Assignment not found', 404);
            }
            
            return $this->successResponse($assignment);
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to retrieve assignment: ' . $e->getMessage(), 500);
        }
    }

    /**
     * @PutMapping(path="assignments/{id}")
     */
    public function updateAssignment(string $id): ResponseInterface
    {
        $data = $this->request->all();
        
        try {
            $result = $this->transportationService->updateAssignment($id, $data);
            
            if (!$result) {
                return $this->errorResponse('Assignment not found', 404);
            }
            
            $assignment = $this->transportationService->getAssignment($id);
            return $this->successResponse($assignment, 'Assignment updated successfully');
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to update assignment: ' . $e->getMessage(), 500);
        }
    }

    /**
     * @DeleteMapping(path="assignments/{id}")
     */
    public function deleteAssignment(string $id): ResponseInterface
    {
        try {
            $result = $this->transportationService->deleteAssignment($id);
            
            if (!$result) {
                return $this->errorResponse('Assignment not found', 404);
            }
            
            return $this->successResponse(null, 'Assignment deleted successfully');
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to delete assignment: ' . $e->getMessage(), 500);
        }
    }

    /**
     * @GetMapping(path="students/{studentId}/assignments")
     */
    public function getStudentAssignments(string $studentId): ResponseInterface
    {
        try {
            $filters = $this->request->all();
            $assignments = $this->transportationService->getStudentAssignments($studentId, $filters);
            return $this->successResponse($assignments);
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to retrieve assignments: ' . $e->getMessage(), 500);
        }
    }

    /**
     * @GetMapping(path="routes/{routeId}/assignments")
     */
    public function getRouteAssignments(string $routeId): ResponseInterface
    {
        try {
            $filters = $this->request->all();
            $assignments = $this->transportationService->getRouteAssignments($routeId, $filters);
            return $this->successResponse($assignments);
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to retrieve assignments: ' . $e->getMessage(), 500);
        }
    }

    /**
     * @PostMapping(path="attendance")
     */
    public function recordAttendance(): ResponseInterface
    {
        $data = $this->request->all();
        
        if (empty($data['assignment_id']) || empty($data['student_id']) || empty($data['route_id']) || empty($data['trip_type']) || empty($data['attendance_date'])) {
            return $this->errorResponse('Assignment ID, student ID, route ID, trip type, and attendance date are required');
        }

        try {
            $attendance = $this->transportationService->recordAttendance($data);
            return $this->successResponse($attendance, 'Attendance recorded successfully');
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to record attendance: ' . $e->getMessage(), 500);
        }
    }

    /**
     * @GetMapping(path="attendance/{id}")
     */
    public function getAttendance(string $id): ResponseInterface
    {
        try {
            $attendance = $this->transportationService->getAttendance($id);
            
            if (!$attendance) {
                return $this->errorResponse('Attendance record not found', 404);
            }
            
            return $this->successResponse($attendance);
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to retrieve attendance: ' . $e->getMessage(), 500);
        }
    }

    /**
     * @GetMapping(path="students/{studentId}/attendance")
     */
    public function getStudentAttendance(string $studentId): ResponseInterface
    {
        $startDate = $this->request->query('start_date');
        $endDate = $this->request->query('end_date');
        
        if (empty($startDate) || empty($endDate)) {
            return $this->errorResponse('Start date and end date are required');
        }

        try {
            $attendance = $this->transportationService->getStudentAttendance($studentId, $startDate, $endDate);
            return $this->successResponse($attendance);
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to retrieve attendance: ' . $e->getMessage(), 500);
        }
    }

    /**
     * @GetMapping(path="routes/{routeId}/attendance")
     */
    public function getRouteAttendance(string $routeId): ResponseInterface
    {
        $date = $this->request->query('date');
        
        if (empty($date)) {
            return $this->errorResponse('Date is required');
        }

        try {
            $attendance = $this->transportationService->getRouteAttendance($routeId, $date);
            return $this->successResponse($attendance);
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to retrieve attendance: ' . $e->getMessage(), 500);
        }
    }

    /**
     * @PostMapping(path="tracking")
     */
    public function recordVehicleLocation(): ResponseInterface
    {
        $data = $this->request->all();
        
        if (empty($data['vehicle_id']) || empty($data['route_id']) || empty($data['latitude']) || empty($data['longitude'])) {
            return $this->errorResponse('Vehicle ID, route ID, latitude, and longitude are required');
        }

        try {
            $tracking = $this->transportationService->recordVehicleLocation($data);
            return $this->successResponse($tracking, 'Vehicle location recorded successfully');
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to record location: ' . $e->getMessage(), 500);
        }
    }

    /**
     * @GetMapping(path="vehicles/{vehicleId}/location")
     */
    public function getVehicleLocation(string $vehicleId): ResponseInterface
    {
        $scheduleId = $this->request->query('schedule_id');
        
        try {
            $location = $this->transportationService->getVehicleLocation($vehicleId, $scheduleId);
            return $this->successResponse($location);
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to retrieve location: ' . $e->getMessage(), 500);
        }
    }

    /**
     * @PostMapping(path="fees")
     */
    public function createFee(): ResponseInterface
    {
        $data = $this->request->all();
        
        if (empty($data['student_id']) || empty($data['amount']) || empty($data['due_date'])) {
            return $this->errorResponse('Student ID, amount, and due date are required');
        }

        try {
            $fee = $this->transportationService->createFee($data);
            return $this->successResponse($fee, 'Fee created successfully');
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to create fee: ' . $e->getMessage(), 500);
        }
    }

    /**
     * @GetMapping(path="fees/{id}")
     */
    public function getFee(string $id): ResponseInterface
    {
        try {
            $fee = $this->transportationService->getFee($id);
            
            if (!$fee) {
                return $this->errorResponse('Fee not found', 404);
            }
            
            return $this->successResponse($fee);
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to retrieve fee: ' . $e->getMessage(), 500);
        }
    }

    /**
     * @PostMapping(path="fees/{id}/pay")
     */
    public function markFeePaid(string $id): ResponseInterface
    {
        $data = $this->request->all();
        
        try {
            $result = $this->transportationService->markFeePaid($id, $data);
            
            if (!$result) {
                return $this->errorResponse('Fee not found', 404);
            }
            
            $fee = $this->transportationService->getFee($id);
            return $this->successResponse($fee, 'Fee marked as paid successfully');
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to mark fee as paid: ' . $e->getMessage(), 500);
        }
    }

    /**
     * @GetMapping(path="students/{studentId}/fees")
     */
    public function getStudentFees(string $studentId): ResponseInterface
    {
        try {
            $filters = $this->request->all();
            $fees = $this->transportationService->getStudentFees($studentId, $filters);
            return $this->successResponse($fees);
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to retrieve fees: ' . $e->getMessage(), 500);
        }
    }

    /**
     * @GetMapping(path="fees/pending")
     */
    public function getPendingFees(): ResponseInterface
    {
        $studentId = $this->request->query('student_id');
        
        try {
            $fees = $this->transportationService->getPendingFees($studentId);
            return $this->successResponse($fees);
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to retrieve pending fees: ' . $e->getMessage(), 500);
        }
    }

    /**
     * @PostMapping(path="notifications")
     */
    public function createNotification(): ResponseInterface
    {
        $data = $this->request->all();
        
        if (empty($data['notification_type']) || empty($data['title']) || empty($data['message'])) {
            return $this->errorResponse('Notification type, title, and message are required');
        }

        try {
            $notification = $this->transportationService->createNotification($data);
            return $this->successResponse($notification, 'Notification created successfully');
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to create notification: ' . $e->getMessage(), 500);
        }
    }

    /**
     * @PostMapping(path="notifications/delay")
     */
    public function createBusDelayNotification(): ResponseInterface
    {
        $data = $this->request->all();
        
        if (empty($data['route_id']) || empty($data['message'])) {
            return $this->errorResponse('Route ID and message are required');
        }

        try {
            $notification = $this->transportationService->createBusDelayNotification($data);
            return $this->successResponse($notification, 'Bus delay notification created successfully');
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to create delay notification: ' . $e->getMessage(), 500);
        }
    }

    /**
     * @PostMapping(path="notifications/emergency")
     */
    public function createEmergencyNotification(): ResponseInterface
    {
        $data = $this->request->all();
        
        if (empty($data['message'])) {
            return $this->errorResponse('Message is required');
        }

        try {
            $notification = $this->transportationService->createEmergencyNotification($data);
            return $this->successResponse($notification, 'Emergency notification created successfully');
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to create emergency notification: ' . $e->getMessage(), 500);
        }
    }

    /**
     * @PostMapping(path="notifications/{id}/send")
     */
    public function sendNotification(string $id): ResponseInterface
    {
        try {
            $result = $this->transportationService->sendNotification($id);
            
            if (!$result) {
                return $this->errorResponse('Notification not found', 404);
            }
            
            $notification = $this->transportationService->getNotification($id);
            return $this->successResponse($notification, 'Notification sent successfully');
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to send notification: ' . $e->getMessage(), 500);
        }
    }

    /**
     * @GetMapping(path="reports/occupancy/{routeId}")
     */
    public function getVehicleOccupancy(string $routeId): ResponseInterface
    {
        try {
            $occupancy = $this->transportationService->getVehicleOccupancy($routeId);
            return $this->successResponse($occupancy);
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to retrieve occupancy: ' . $e->getMessage(), 500);
        }
    }

    /**
     * @GetMapping(path="reports/analytics/{routeId}")
     */
    public function getRouteAnalytics(string $routeId): ResponseInterface
    {
        $startDate = $this->request->query('start_date');
        $endDate = $this->request->query('end_date');
        
        if (empty($startDate) || empty($endDate)) {
            return $this->errorResponse('Start date and end date are required');
        }

        try {
            $analytics = $this->transportationService->getRouteAnalytics($routeId, $startDate, $endDate);
            return $this->successResponse($analytics);
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to retrieve analytics: ' . $e->getMessage(), 500);
        }
    }

    /**
     * @GetMapping(path="reports/summary")
     */
    public function getTransportationReport(): ResponseInterface
    {
        try {
            $report = $this->transportationService->getTransportationReport();
            return $this->successResponse($report);
        } catch (\Exception $e) {
            return $this->errorResponse('Failed to retrieve report: ' . $e->getMessage(), 500);
        }
    }
}
