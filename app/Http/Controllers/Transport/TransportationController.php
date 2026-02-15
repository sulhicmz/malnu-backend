<?php

declare(strict_types=1);

namespace App\Http\Controllers\Transport;

use App\Http\Controllers\AbstractController;
use App\Services\TransportationService;
use Hypervel\Router\Annotation\Controller;
use Hypervel\Router\Annotation\GetMapping;
use Hypervel\Router\Annotation\PostMapping;
use Hypervel\Router\Annotation\PutMapping;
use Hypervel\Router\Annotation\DeleteMapping;
use Hypervel\Router\Annotation\Middleware;
use App\Http\Middleware\JWTMiddleware;
use Psr\Http\Message\ResponseInterface;

/**
 * @Controller(prefix="api/transport")
 * @Middleware(JWTMiddleware::class)
 */
class TransportationController extends AbstractController
{
    private TransportationService $transportationService;

    public function __construct(TransportationService $transportationService)
    {
        $this->transportationService = $transportationService;
    }

    /**
     * Create vehicle
     * @PostMapping(path="vehicles")
     */
    public function createVehicle(): ResponseInterface
    {
        $data = $this->request->all();

        try {
            $vehicle = $this->transportationService->createVehicle($data);
            return $this->successResponse($vehicle, 'Vehicle created successfully');
        } catch (\Exception $e) {
            return $this->serverErrorResponse('Failed to create vehicle: ' . $e->getMessage());
        }
    }

    /**
     * Get all vehicles
     * @GetMapping(path="vehicles")
     */
    public function getVehicles(): ResponseInterface
    {
        try {
            $filters = $this->request->all();
            $vehicles = $this->transportationService->getVehicles($filters);
            return $this->successResponse($vehicles, 'Vehicles retrieved successfully');
        } catch (\Exception $e) {
            return $this->serverErrorResponse('Failed to retrieve vehicles: ' . $e->getMessage());
        }
    }

    /**
     * Get vehicle by ID
     * @GetMapping(path="vehicles/{id}")
     */
    public function getVehicle(string $id): ResponseInterface
    {
        try {
            $vehicle = $this->transportationService->getVehicle($id);
            if (!$vehicle) {
                return $this->notFoundResponse('Vehicle not found');
            }

            return $this->successResponse($vehicle, 'Vehicle retrieved successfully');
        } catch (\Exception $e) {
            return $this->serverErrorResponse('Failed to retrieve vehicle: ' . $e->getMessage());
        }
    }

    /**
     * Update vehicle
     * @PutMapping(path="vehicles/{id}")
     */
    public function updateVehicle(string $id): ResponseInterface
    {
        $data = $this->request->all();

        try {
            $vehicle = $this->transportationService->updateVehicle($id, $data);
            if (!$vehicle) {
                return $this->notFoundResponse('Vehicle not found');
            }

            return $this->successResponse($vehicle, 'Vehicle updated successfully');
        } catch (\Exception $e) {
            return $this->serverErrorResponse('Failed to update vehicle: ' . $e->getMessage());
        }
    }

    /**
     * Delete vehicle
     * @DeleteMapping(path="vehicles/{id}")
     */
    public function deleteVehicle(string $id): ResponseInterface
    {
        try {
            $deleted = $this->transportationService->deleteVehicle($id);
            if (!$deleted) {
                return $this->notFoundResponse('Vehicle not found');
            }

            return $this->successResponse(null, 'Vehicle deleted successfully');
        } catch (\Exception $e) {
            return $this->serverErrorResponse('Failed to delete vehicle: ' . $e->getMessage());
        }
    }

    /**
     * Create driver
     * @PostMapping(path="drivers")
     */
    public function createDriver(): ResponseInterface
    {
        $data = $this->request->all();

        try {
            $driver = $this->transportationService->createDriver($data);
            return $this->successResponse($driver, 'Driver created successfully');
        } catch (\Exception $e) {
            return $this->serverErrorResponse('Failed to create driver: ' . $e->getMessage());
        }
    }

    /**
     * Get all drivers
     * @GetMapping(path="drivers")
     */
    public function getDrivers(): ResponseInterface
    {
        try {
            $filters = $this->request->all();
            $drivers = $this->transportationService->getDrivers($filters);
            return $this->successResponse($drivers, 'Drivers retrieved successfully');
        } catch (\Exception $e) {
            return $this->serverErrorResponse('Failed to retrieve drivers: ' . $e->getMessage());
        }
    }

    /**
     * Get driver by ID
     * @GetMapping(path="drivers/{id}")
     */
    public function getDriver(string $id): ResponseInterface
    {
        try {
            $driver = $this->transportationService->getDriver($id);
            if (!$driver) {
                return $this->notFoundResponse('Driver not found');
            }

            return $this->successResponse($driver, 'Driver retrieved successfully');
        } catch (\Exception $e) {
            return $this->serverErrorResponse('Failed to retrieve driver: ' . $e->getMessage());
        }
    }

    /**
     * Update driver
     * @PutMapping(path="drivers/{id}")
     */
    public function updateDriver(string $id): ResponseInterface
    {
        $data = $this->request->all();

        try {
            $driver = $this->transportationService->updateDriver($id, $data);
            if (!$driver) {
                return $this->notFoundResponse('Driver not found');
            }

            return $this->successResponse($driver, 'Driver updated successfully');
        } catch (\Exception $e) {
            return $this->serverErrorResponse('Failed to update driver: ' . $e->getMessage());
        }
    }

    /**
     * Create stop
     * @PostMapping(path="stops")
     */
    public function createStop(): ResponseInterface
    {
        $data = $this->request->all();

        try {
            $stop = $this->transportationService->createStop($data);
            return $this->successResponse($stop, 'Stop created successfully');
        } catch (\Exception $e) {
            return $this->serverErrorResponse('Failed to create stop: ' . $e->getMessage());
        }
    }

    /**
     * Get all stops
     * @GetMapping(path="stops")
     */
    public function getStops(): ResponseInterface
    {
        try {
            $filters = $this->request->all();
            $stops = $this->transportationService->getStops($filters);
            return $this->successResponse($stops, 'Stops retrieved successfully');
        } catch (\Exception $e) {
            return $this->serverErrorResponse('Failed to retrieve stops: ' . $e->getMessage());
        }
    }

    /**
     * Get nearby stops
     * @GetMapping(path="stops/nearby")
     */
    public function getNearbyStops(): ResponseInterface
    {
        $latitude = (float) $this->request->input('latitude');
        $longitude = (float) $this->request->input('longitude');
        $radius = (float) $this->request->input('radius', 5);

        try {
            $stops = $this->transportationService->getNearbyStops($latitude, $longitude, $radius);
            return $this->successResponse($stops, 'Nearby stops retrieved successfully');
        } catch (\Exception $e) {
            return $this->serverErrorResponse('Failed to retrieve nearby stops: ' . $e->getMessage());
        }
    }

    /**
     * Create route
     * @PostMapping(path="routes")
     */
    public function createRoute(): ResponseInterface
    {
        $data = $this->request->all();

        try {
            $route = $this->transportationService->createRoute($data);
            return $this->successResponse($route, 'Route created successfully');
        } catch (\Exception $e) {
            return $this->serverErrorResponse('Failed to create route: ' . $e->getMessage());
        }
    }

    /**
     * Get all routes
     * @GetMapping(path="routes")
     */
    public function getRoutes(): ResponseInterface
    {
        try {
            $filters = $this->request->all();
            $routes = $this->transportationService->getRoutes($filters);
            return $this->successResponse($routes, 'Routes retrieved successfully');
        } catch (\Exception $e) {
            return $this->serverErrorResponse('Failed to retrieve routes: ' . $e->getMessage());
        }
    }

    /**
     * Create schedule
     * @PostMapping(path="schedules")
     */
    public function createSchedule(): ResponseInterface
    {
        $data = $this->request->all();

        try {
            $schedule = $this->transportationService->createSchedule($data);
            return $this->successResponse($schedule, 'Schedule created successfully');
        } catch (\Exception $e) {
            return $this->serverErrorResponse('Failed to create schedule: ' . $e->getMessage());
        }
    }

    /**
     * Get all schedules
     * @GetMapping(path="schedules")
     */
    public function getSchedules(): ResponseInterface
    {
        try {
            $filters = $this->request->all();
            $schedules = $this->transportationService->getSchedules($filters);
            return $this->successResponse($schedules, 'Schedules retrieved successfully');
        } catch (\Exception $e) {
            return $this->serverErrorResponse('Failed to retrieve schedules: ' . $e->getMessage());
        }
    }

    /**
     * Assign student to route
     * @PostMapping(path="assignments")
     */
    public function assignStudent(): ResponseInterface
    {
        $data = $this->request->all();

        try {
            $assignment = $this->transportationService->assignStudentToRoute(
                $data['student_id'],
                $data['route_id'],
                $data
            );
            return $this->successResponse($assignment, 'Student assigned successfully');
        } catch (\Exception $e) {
            return $this->serverErrorResponse('Failed to assign student: ' . $e->getMessage());
        }
    }

    /**
     * Get student assignments
     * @GetMapping(path="assignments/student/{studentId}")
     */
    public function getStudentAssignments(string $studentId): ResponseInterface
    {
        try {
            $assignments = $this->transportationService->getStudentAssignments($studentId);
            return $this->successResponse($assignments, 'Assignments retrieved successfully');
        } catch (\Exception $e) {
            return $this->serverErrorResponse('Failed to retrieve assignments: ' . $e->getMessage());
        }
    }

    /**
     * Record attendance
     * @PostMapping(path="attendance")
     */
    public function recordAttendance(): ResponseInterface
    {
        $data = $this->request->all();

        try {
            $attendance = $this->transportationService->recordAttendance(
                $data['assignment_id'],
                $data['route_id'],
                $data['student_id'],
                $data
            );
            return $this->successResponse($attendance, 'Attendance recorded successfully');
        } catch (\Exception $e) {
            return $this->serverErrorResponse('Failed to record attendance: ' . $e->getMessage());
        }
    }

    /**
     * Get today's attendance
     * @GetMapping(path="attendance/today/{routeId}")
     */
    public function getTodayAttendance(string $routeId): ResponseInterface
    {
        try {
            $date = $this->request->input('date');
            $attendance = $this->transportationService->getTodayAttendance($routeId, $date);
            return $this->successResponse($attendance, 'Attendance retrieved successfully');
        } catch (\Exception $e) {
            return $this->serverErrorResponse('Failed to retrieve attendance: ' . $e->getMessage());
        }
    }

    /**
     * Record GPS location
     * @PostMapping(path="tracking")
     */
    public function recordTracking(): ResponseInterface
    {
        $data = $this->request->all();

        try {
            $tracking = $this->transportationService->recordTracking($data['vehicle_id'], $data);
            return $this->successResponse($tracking, 'Location recorded successfully');
        } catch (\Exception $e) {
            return $this->serverErrorResponse('Failed to record location: ' . $e->getMessage());
        }
    }

    /**
     * Get active vehicle locations
     * @GetMapping(path="tracking/active")
     */
    public function getActiveLocations(): ResponseInterface
    {
        try {
            $locations = $this->transportationService->getActiveVehicleLocations();
            return $this->successResponse($locations, 'Active locations retrieved successfully');
        } catch (\Exception $e) {
            return $this->serverErrorResponse('Failed to retrieve active locations: ' . $e->getMessage());
        }
    }

    /**
     * Report incident
     * @PostMapping(path="incidents")
     */
    public function reportIncident(): ResponseInterface
    {
        $data = $this->request->all();

        try {
            $incident = $this->transportationService->reportIncident($data);
            return $this->successResponse($incident, 'Incident reported successfully');
        } catch (\Exception $e) {
            return $this->serverErrorResponse('Failed to report incident: ' . $e->getMessage());
        }
    }

    /**
     * Get incidents
     * @GetMapping(path="incidents")
     */
    public function getIncidents(): ResponseInterface
    {
        try {
            $filters = $this->request->all();
            $incidents = $this->transportationService->getIncidents($filters);
            return $this->successResponse($incidents, 'Incidents retrieved successfully');
        } catch (\Exception $e) {
            return $this->serverErrorResponse('Failed to retrieve incidents: ' . $e->getMessage());
        }
    }

    /**
     * Resolve incident
     * @PostMapping(path="incidents/{id}/resolve")
     */
    public function resolveIncident(string $id): ResponseInterface
    {
        $data = $this->request->all();

        try {
            $incident = $this->transportationService->resolveIncident($id, $data['resolution']);
            if (!$incident) {
                return $this->notFoundResponse('Incident not found');
            }

            return $this->successResponse($incident, 'Incident resolved successfully');
        } catch (\Exception $e) {
            return $this->serverErrorResponse('Failed to resolve incident: ' . $e->getMessage());
        }
    }

    /**
     * Get statistics
     * @GetMapping(path="statistics")
     */
    public function getStatistics(): ResponseInterface
    {
        try {
            $stats = $this->transportationService->getStatistics();
            return $this->successResponse($stats, 'Statistics retrieved successfully');
        } catch (\Exception $e) {
            return $this->serverErrorResponse('Failed to retrieve statistics: ' . $e->getMessage());
        }
    }
}