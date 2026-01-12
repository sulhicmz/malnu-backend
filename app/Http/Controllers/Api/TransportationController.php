<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\BaseController;
use App\Services\TransportationService;
use Hyperf\HttpServer\Contract\RequestInterface;
use Hyperf\HttpServer\Contract\ResponseInterface;
use Psr\Container\ContainerInterface;
use Hyperf\Validation\Contract\ValidatorFactoryInterface;
use Exception;

class TransportationController extends BaseController
{
    private TransportationService $transportationService;
    private ValidatorFactoryInterface $validator;

    public function __construct(
        RequestInterface $request,
        ResponseInterface $response,
        ContainerInterface $container,
        TransportationService $transportationService,
        ValidatorFactoryInterface $validator
    ) {
        parent::__construct($request, $response, $container);
        $this->transportationService = $transportationService;
        $this->validator = $validator;
    }

    public function createVehicle(RequestInterface $request, ResponseInterface $response)
    {
        try {
            $validator = $this->validator->make(
                $request->all(),
                [
                    'plate_number' => 'required|string|max:50|unique:transport_vehicles,plate_number',
                    'vehicle_type' => 'required|in:bus,van,minibus',
                    'make' => 'required|string|max:100',
                    'model' => 'required|string|max:100',
                    'year' => 'nullable|integer|min:2000|max:2099',
                    'capacity' => 'required|integer|min:1|max:100',
                    'color' => 'nullable|string|max:50',
                    'vin' => 'nullable|string|max:100',
                    'registration_number' => 'nullable|string|max:100',
                    'registration_expiry' => 'nullable|date',
                    'insurance_expiry' => 'nullable|date',
                    'inspection_expiry' => 'nullable|date',
                    'fuel_type' => 'required|in:diesel,petrol,electric,hybrid',
                    'status' => 'nullable|in:active,maintenance,retired',
                ]
            );

            if ($validator->fails()) {
                return $this->validationErrorResponse($validator->errors());
            }

            $vehicle = $this->transportationService->createVehicle([
                ...$request->all(),
                'created_by' => $request->getAttribute('user_id'),
            ]);

            return $this->successResponse($vehicle, 'Vehicle created successfully');
        } catch (Exception $e) {
            $this->logger->error('Failed to create vehicle: ' . $e->getMessage());
            return $this->serverErrorResponse('Failed to create vehicle');
        }
    }

    public function updateVehicle(RequestInterface $request, ResponseInterface $response, string $id)
    {
        try {
            $vehicle = $this->transportationService->getVehicle($id);
            if (!$vehicle) {
                return $this->notFoundResponse('Vehicle not found');
            }

            $validator = $this->validator->make(
                $request->all(),
                [
                    'plate_number' => 'sometimes|required|string|max:50|unique:transport_vehicles,plate_number,' . $id,
                    'vehicle_type' => 'sometimes|required|in:bus,van,minibus',
                    'make' => 'sometimes|required|string|max:100',
                    'model' => 'sometimes|required|string|max:100',
                    'year' => 'nullable|integer|min:2000|max:2099',
                    'capacity' => 'sometimes|required|integer|min:1|max:100',
                    'color' => 'nullable|string|max:50',
                    'vin' => 'nullable|string|max:100',
                    'registration_number' => 'nullable|string|max:100',
                    'registration_expiry' => 'nullable|date',
                    'insurance_expiry' => 'nullable|date',
                    'inspection_expiry' => 'nullable|date',
                    'fuel_type' => 'sometimes|required|in:diesel,petrol,electric,hybrid',
                    'status' => 'nullable|in:active,maintenance,retired',
                ]
            );

            if ($validator->fails()) {
                return $this->validationErrorResponse($validator->errors());
            }

            $updated = $this->transportationService->updateVehicle($id, [
                ...$request->all(),
                'updated_by' => $request->getAttribute('user_id'),
            ]);

            if (!$updated) {
                return $this->errorResponse('Failed to update vehicle');
            }

            return $this->successResponse($this->transportationService->getVehicle($id), 'Vehicle updated successfully');
        } catch (Exception $e) {
            $this->logger->error('Failed to update vehicle: ' . $e->getMessage());
            return $this->serverErrorResponse('Failed to update vehicle');
        }
    }

    public function deleteVehicle(ResponseInterface $response, string $id)
    {
        try {
            $vehicle = $this->transportationService->getVehicle($id);
            if (!$vehicle) {
                return $this->notFoundResponse('Vehicle not found');
            }

            $deleted = $this->transportationService->deleteVehicle($id);
            if (!$deleted) {
                return $this->errorResponse('Failed to delete vehicle');
            }

            return $this->successResponse(null, 'Vehicle deleted successfully');
        } catch (Exception $e) {
            $this->logger->error('Failed to delete vehicle: ' . $e->getMessage());
            return $this->serverErrorResponse('Failed to delete vehicle');
        }
    }

    public function getVehicle(ResponseInterface $response, string $id)
    {
        try {
            $vehicle = $this->transportationService->getVehicle($id);
            if (!$vehicle) {
                return $this->notFoundResponse('Vehicle not found');
            }

            return $this->successResponse($vehicle);
        } catch (Exception $e) {
            $this->logger->error('Failed to get vehicle: ' . $e->getMessage());
            return $this->serverErrorResponse('Failed to get vehicle');
        }
    }

    public function listVehicles(RequestInterface $request)
    {
        try {
            $filters = [
                'vehicle_type' => $request->input('vehicle_type'),
            ];

            $vehicles = $this->transportationService->getActiveVehicles($filters);

            return $this->successResponse($vehicles);
        } catch (Exception $e) {
            $this->logger->error('Failed to list vehicles: ' . $e->getMessage());
            return $this->serverErrorResponse('Failed to list vehicles');
        }
    }

    public function createDriver(RequestInterface $request, ResponseInterface $response)
    {
        try {
            $validator = $this->validator->make(
                $request->all(),
                [
                    'employee_id' => 'nullable|string|max:50|unique:transport_drivers,employee_id',
                    'name' => 'required|string|max:255',
                    'phone' => 'required|string|max:20',
                    'email' => 'nullable|email|max:255',
                    'license_number' => 'required|string|max:100|unique:transport_drivers,license_number',
                    'license_type' => 'required|in:commercial,bus,heavy_vehicle',
                    'license_expiry' => 'required|date',
                    'address' => 'nullable|string|max:500',
                    'hire_date' => 'required|date',
                    'status' => 'nullable|in:active,inactive,on_leave',
                ]
            );

            if ($validator->fails()) {
                return $this->validationErrorResponse($validator->errors());
            }

            $driver = $this->transportationService->createDriver([
                ...$request->all(),
                'created_by' => $request->getAttribute('user_id'),
            ]);

            return $this->successResponse($driver, 'Driver created successfully');
        } catch (Exception $e) {
            $this->logger->error('Failed to create driver: ' . $e->getMessage());
            return $this->serverErrorResponse('Failed to create driver');
        }
    }

    public function updateDriver(RequestInterface $request, ResponseInterface $response, string $id)
    {
        try {
            $driver = $this->transportationService->getDriver($id);
            if (!$driver) {
                return $this->notFoundResponse('Driver not found');
            }

            $validator = $this->validator->make(
                $request->all(),
                [
                    'employee_id' => 'sometimes|nullable|string|max:50|unique:transport_drivers,employee_id,' . $id,
                    'name' => 'sometimes|required|string|max:255',
                    'phone' => 'sometimes|required|string|max:20',
                    'email' => 'nullable|email|max:255',
                    'license_number' => 'sometimes|required|string|max:100|unique:transport_drivers,license_number,' . $id,
                    'license_type' => 'sometimes|required|in:commercial,bus,heavy_vehicle',
                    'license_expiry' => 'sometimes|required|date',
                    'address' => 'nullable|string|max:500',
                    'hire_date' => 'sometimes|required|date',
                    'status' => 'nullable|in:active,inactive,on_leave',
                ]
            );

            if ($validator->fails()) {
                return $this->validationErrorResponse($validator->errors());
            }

            $updated = $this->transportationService->updateDriver($id, [
                ...$request->all(),
                'updated_by' => $request->getAttribute('user_id'),
            ]);

            if (!$updated) {
                return $this->errorResponse('Failed to update driver');
            }

            return $this->successResponse($this->transportationService->getDriver($id), 'Driver updated successfully');
        } catch (Exception $e) {
            $this->logger->error('Failed to update driver: ' . $e->getMessage());
            return $this->serverErrorResponse('Failed to update driver');
        }
    }

    public function getDriver(ResponseInterface $response, string $id)
    {
        try {
            $driver = $this->transportationService->getDriver($id);
            if (!$driver) {
                return $this->notFoundResponse('Driver not found');
            }

            return $this->successResponse($driver);
        } catch (Exception $e) {
            $this->logger->error('Failed to get driver: ' . $e->getMessage());
            return $this->serverErrorResponse('Failed to get driver');
        }
    }

    public function listDrivers(RequestInterface $request)
    {
        try {
            $drivers = $this->transportationService->getActiveDrivers();
            return $this->successResponse($drivers);
        } catch (Exception $e) {
            $this->logger->error('Failed to list drivers: ' . $e->getMessage());
            return $this->serverErrorResponse('Failed to list drivers');
        }
    }

    public function createRoute(RequestInterface $request, ResponseInterface $response)
    {
        try {
            $validator = $this->validator->make(
                $request->all(),
                [
                    'route_name' => 'required|string|max:255',
                    'route_number' => 'required|string|max:50|unique:transport_routes,route_number',
                    'description' => 'nullable|string',
                    'route_type' => 'nullable|in:regular,express,special',
                    'start_time' => 'required|date_format:H:i:s',
                    'end_time' => 'required|date_format:H:i:s',
                    'total_distance' => 'nullable|numeric|min:0',
                    'estimated_duration' => 'nullable|integer|min:0',
                    'stops' => 'required|array|min:2',
                    'stops.*.stop_name' => 'required|string|max:255',
                    'stops.*.description' => 'nullable|string',
                    'stops.*.address' => 'nullable|string',
                    'stops.*.latitude' => 'required|numeric|between:-90,90',
                    'stops.*.longitude' => 'required|numeric|between:-180,180',
                    'stops.*.arrival_time' => 'nullable|date_format:H:i:s',
                    'stops.*.departure_time' => 'nullable|date_format:H:i:s',
                    'stops.*.is_morning' => 'nullable|boolean',
                    'stops.*.is_afternoon' => 'nullable|boolean',
                ]
            );

            if ($validator->fails()) {
                return $this->validationErrorResponse($validator->errors());
            }

            $route = $this->transportationService->createRoute([
                ...$request->all(),
                'created_by' => $request->getAttribute('user_id'),
            ]);

            return $this->successResponse($route, 'Route created successfully');
        } catch (Exception $e) {
            $this->logger->error('Failed to create route: ' . $e->getMessage());
            return $this->serverErrorResponse('Failed to create route');
        }
    }

    public function getRoute(ResponseInterface $response, string $id)
    {
        try {
            $route = $this->transportationService->getRoute($id);
            if (!$route) {
                return $this->notFoundResponse('Route not found');
            }

            return $this->successResponse($route);
        } catch (Exception $e) {
            $this->logger->error('Failed to get route: ' . $e->getMessage());
            return $this->serverErrorResponse('Failed to get route');
        }
    }

    public function listRoutes(RequestInterface $request)
    {
        try {
            $routes = $this->transportationService->getActiveRoutes();
            return $this->successResponse($routes);
        } catch (Exception $e) {
            $this->logger->error('Failed to list routes: ' . $e->getMessage());
            return $this->serverErrorResponse('Failed to list routes');
        }
    }

    public function assignStudent(RequestInterface $request, ResponseInterface $response)
    {
        try {
            $validator = $this->validator->make(
                $request->all(),
                [
                    'student_id' => 'required|string|exists:students,id',
                    'route_id' => 'required|string|exists:transport_routes,id',
                    'stop_id' => 'required|string|exists:transport_stops,id',
                    'vehicle_id' => 'nullable|string|exists:transport_vehicles,id',
                    'driver_id' => 'nullable|string|exists:transport_drivers,id',
                    'effective_date' => 'nullable|date',
                    'end_date' => 'nullable|date|after:effective_date',
                    'session_type' => 'nullable|in:morning,afternoon,both',
                    'fee_status' => 'nullable|in:pending,paid,exempt',
                    'monthly_fee' => 'nullable|numeric|min:0',
                ]
            );

            if ($validator->fails()) {
                return $this->validationErrorResponse($validator->errors());
            }

            $assignment = $this->transportationService->assignStudent([
                ...$request->all(),
                'created_by' => $request->getAttribute('user_id'),
            ]);

            return $this->successResponse($assignment, 'Student assigned successfully');
        } catch (Exception $e) {
            $this->logger->error('Failed to assign student: ' . $e->getMessage());
            return $this->serverErrorResponse('Failed to assign student');
        }
    }

    public function getAssignment(ResponseInterface $response, string $id)
    {
        try {
            $assignment = $this->transportationService->getAssignment($id);
            if (!$assignment) {
                return $this->notFoundResponse('Assignment not found');
            }

            return $this->successResponse($assignment);
        } catch (Exception $e) {
            $this->logger->error('Failed to get assignment: ' . $e->getMessage());
            return $this->serverErrorResponse('Failed to get assignment');
        }
    }

    public function getStudentAssignments(ResponseInterface $response, string $studentId)
    {
        try {
            $assignments = $this->transportationService->getStudentAssignments($studentId);
            return $this->successResponse($assignments);
        } catch (Exception $e) {
            $this->logger->error('Failed to get student assignments: ' . $e->getMessage());
            return $this->serverErrorResponse('Failed to get student assignments');
        }
    }

    public function recordAttendance(RequestInterface $request, ResponseInterface $response)
    {
        try {
            $validator = $this->validator->make(
                $request->all(),
                [
                    'assignment_id' => 'required|string|exists:transport_assignments,id',
                    'attendance_date' => 'required|date',
                    'session_type' => 'required|in:morning,afternoon',
                    'boarding_status' => 'required|in:pending,boarded,missed,excused',
                    'boarding_time' => 'nullable|date_format:H:i:s',
                    'alighting_time' => 'nullable|date_format:H:i:s',
                    'boarding_stop_id' => 'nullable|string|exists:transport_stops,id',
                    'alighting_stop_id' => 'nullable|string|exists:transport_stops,id',
                    'notes' => 'nullable|string',
                ]
            );

            if ($validator->fails()) {
                return $this->validationErrorResponse($validator->errors());
            }

            $attendance = $this->transportationService->recordAttendance([
                ...$request->all(),
                'recorded_by' => $request->getAttribute('user_id'),
            ]);

            return $this->successResponse($attendance, 'Attendance recorded successfully');
        } catch (Exception $e) {
            $this->logger->error('Failed to record attendance: ' . $e->getMessage());
            return $this->serverErrorResponse('Failed to record attendance');
        }
    }

    public function getTodayAttendance(RequestInterface $request)
    {
        try {
            $filters = [
                'session_type' => $request->input('session_type'),
                'boarding_status' => $request->input('boarding_status'),
            ];

            $attendance = $this->transportationService->getTodayAttendance($filters);

            return $this->successResponse($attendance);
        } catch (Exception $e) {
            $this->logger->error('Failed to get today attendance: ' . $e->getMessage());
            return $this->serverErrorResponse('Failed to get today attendance');
        }
    }

    public function reportIncident(RequestInterface $request, ResponseInterface $response)
    {
        try {
            $validator = $this->validator->make(
                $request->all(),
                [
                    'vehicle_id' => 'nullable|string|exists:transport_vehicles,id',
                    'driver_id' => 'nullable|string|exists:transport_drivers,id',
                    'route_id' => 'nullable|string|exists:transport_routes,id',
                    'incident_type' => 'required|in:accident,breakdown,delay,medical,safety_issue',
                    'severity' => 'required|in:minor,moderate,major,critical',
                    'incident_time' => 'required|date',
                    'description' => 'required|string',
                    'latitude' => 'nullable|numeric|between:-90,90',
                    'longitude' => 'nullable|numeric|between:-180,180',
                    'location_address' => 'nullable|string|max:500',
                    'actions_taken' => 'nullable|string',
                    'follow_up_required' => 'nullable|string',
                    'students_involved' => 'nullable|integer|min:0',
                    'student_ids' => 'nullable|array',
                    'student_ids.*' => 'string|exists:students,id',
                    'evidence_photos' => 'nullable|array',
                ]
            );

            if ($validator->fails()) {
                return $this->validationErrorResponse($validator->errors());
            }

            $incident = $this->transportationService->reportIncident([
                ...$request->all(),
                'reported_by' => $request->getAttribute('user_id'),
                'created_by' => $request->getAttribute('user_id'),
            ]);

            return $this->successResponse($incident, 'Incident reported successfully');
        } catch (Exception $e) {
            $this->logger->error('Failed to report incident: ' . $e->getMessage());
            return $this->serverErrorResponse('Failed to report incident');
        }
    }

    public function getIncident(ResponseInterface $response, string $id)
    {
        try {
            $incident = $this->transportationService->getIncident($id);
            if (!$incident) {
                return $this->notFoundResponse('Incident not found');
            }

            return $this->successResponse($incident);
        } catch (Exception $e) {
            $this->logger->error('Failed to get incident: ' . $e->getMessage());
            return $this->serverErrorResponse('Failed to get incident');
        }
    }

    public function getOpenIncidents(RequestInterface $request)
    {
        try {
            $incidents = $this->transportationService->getOpenIncidents();
            return $this->successResponse($incidents);
        } catch (Exception $e) {
            $this->logger->error('Failed to get open incidents: ' . $e->getMessage());
            return $this->serverErrorResponse('Failed to get open incidents');
        }
    }

    public function recordLocation(RequestInterface $request, ResponseInterface $response)
    {
        try {
            $validator = $this->validator->make(
                $request->all(),
                [
                    'vehicle_id' => 'required|string|exists:transport_vehicles,id',
                    'route_id' => 'nullable|string|exists:transport_routes,id',
                    'driver_id' => 'nullable|string|exists:transport_drivers,id',
                    'latitude' => 'required|numeric|between:-90,90',
                    'longitude' => 'required|numeric|between:-180,180',
                    'speed' => 'nullable|numeric|min:0|max:200',
                    'heading' => 'nullable|numeric|between:0,360',
                    'altitude' => 'nullable|numeric',
                    'ignition_on' => 'nullable|boolean',
                    'odometer' => 'nullable|numeric|min:0',
                    'recorded_at' => 'nullable|date',
                ]
            );

            if ($validator->fails()) {
                return $this->validationErrorResponse($validator->errors());
            }

            $tracking = $this->transportationService->recordLocation($request->all());

            return $this->successResponse($tracking, 'Location recorded successfully');
        } catch (Exception $e) {
            $this->logger->error('Failed to record location: ' . $e->getMessage());
            return $this->serverErrorResponse('Failed to record location');
        }
    }

    public function getVehicleLocation(ResponseInterface $response, string $vehicleId)
    {
        try {
            $location = $this->transportationService->getVehicleLocation($vehicleId);
            if (!$location) {
                return $this->notFoundResponse('No location data found for this vehicle');
            }

            return $this->successResponse($location);
        } catch (Exception $e) {
            $this->logger->error('Failed to get vehicle location: ' . $e->getMessage());
            return $this->serverErrorResponse('Failed to get vehicle location');
        }
    }

    public function getVehiclesWithLocations(RequestInterface $request)
    {
        try {
            $minutes = $request->input('minutes', 10);
            $vehicles = $this->transportationService->getVehiclesWithRecentLocations($minutes);

            return $this->successResponse($vehicles);
        } catch (Exception $e) {
            $this->logger->error('Failed to get vehicles with locations: ' . $e->getMessage());
            return $this->serverErrorResponse('Failed to get vehicles with locations');
        }
    }

    public function getStats(RequestInterface $request)
    {
        try {
            $stats = $this->transportationService->getTransportationStats();
            return $this->successResponse($stats);
        } catch (Exception $e) {
            $this->logger->error('Failed to get transportation stats: ' . $e->getMessage());
            return $this->serverErrorResponse('Failed to get transportation stats');
        }
    }
}
