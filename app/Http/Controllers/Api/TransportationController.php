<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\BaseController;
use App\Services\TransportationManagementService;
use Hyperf\HttpServer\Contract\RequestInterface;
use Hyperf\HttpServer\Contract\ResponseInterface;
use Psr\Container\ContainerInterface;

/**
 * @OA\Tag(
 *     name="Transportation",
 *     description="Transportation management endpoints"
 * )
 */
class TransportationController extends BaseController
{
    protected string $resourceName = 'Transportation';

    public function __construct(
        RequestInterface $request,
        ResponseInterface $response,
        ContainerInterface $container
    ) {
        parent::__construct($request, $response, $container);
        $this->service = new TransportationManagementService();
    }

    public function index()
    {
        try {
            $filters = $this->request->all();
            $routes = $this->service->getAllRoutes($filters);

            return $this->successResponse($routes, 'Transportation routes retrieved successfully');
        } catch (\Exception $e) {
            return $this->serverErrorResponse($e->getMessage());
        }
    }

    public function store()
    {
        try {
            $data = $this->request->all();
            $errors = $this->validateRouteData($data);

            if (! empty($errors)) {
                return $this->validationErrorResponse($errors);
            }

            $route = $this->service->createRoute($data);

            return $this->successResponse($route, 'Route created successfully', 201);
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 'ROUTE_CREATION_ERROR', null, 400);
        }
    }

    public function show(string $id)
    {
        try {
            $route = $this->service->getAllRoutes([])->firstWhere('id', $id);

            if (! $route) {
                return $this->notFoundResponse('Route not found');
            }

            return $this->successResponse($route, 'Route retrieved successfully');
        } catch (\Exception $e) {
            return $this->serverErrorResponse($e->getMessage());
        }
    }

    public function update(string $id)
    {
        try {
            $data = $this->request->all();
            $errors = $this->validateRouteData($data);

            if (! empty($errors)) {
                return $this->validationErrorResponse($errors);
            }

            $route = $this->service->updateRoute($id, $data);

            return $this->successResponse($route, 'Route updated successfully');
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 'ROUTE_UPDATE_ERROR', null, 400);
        }
    }

    public function destroy(string $id)
    {
        try {
            $this->service->deleteRoute($id);

            return $this->successResponse(null, 'Route deleted successfully');
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 'ROUTE_DELETION_ERROR', null, 400);
        }
    }

    public function registerStudent(string $studentId, string $routeId)
    {
        try {
            $data = $this->request->all();
            $errors = $this->validateRegistrationData($data);

            if (! empty($errors)) {
                return $this->validationErrorResponse($errors);
            }

            $registration = $this->service->registerStudent($studentId, $routeId, $data);

            return $this->successResponse($registration, 'Student registered for transportation successfully', 201);
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 'REGISTRATION_ERROR', null, 400);
        }
    }

    public function getStudentRegistrations(string $studentId)
    {
        try {
            $registrations = $this->service->getStudentRegistrations($studentId);

            return $this->successResponse($registrations, 'Student registrations retrieved successfully');
        } catch (\Exception $e) {
            return $this->serverErrorResponse($e->getMessage());
        }
    }

    public function assignDriver(string $registrationId)
    {
        try {
            $data = $this->request->all();
            $errors = $this->validateAssignmentData($data);

            if (! empty($errors)) {
                return $this->validationErrorResponse($errors);
            }

            $assignment = $this->service->assignDriver($registrationId, $data['driver_id'] ?? null, $data['vehicle_id'] ?? null, $data);

            return $this->successResponse($assignment, 'Driver assigned successfully');
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 'ASSIGNMENT_ERROR', null, 400);
        }
    }

    public function vehicles()
    {
        try {
            $vehicles = $this->service->getAvailableVehicles();

            return $this->successResponse($vehicles, 'Vehicles retrieved successfully');
        } catch (\Exception $e) {
            return $this->serverErrorResponse($e->getMessage());
        }
    }

    public function storeVehicle()
    {
        try {
            $data = $this->request->all();
            $errors = $this->validateVehicleData($data);

            if (! empty($errors)) {
                return $this->validationErrorResponse($errors);
            }

            $vehicle = $this->service->createVehicle($data);

            return $this->successResponse($vehicle, 'Vehicle created successfully', 201);
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 'VEHICLE_CREATION_ERROR', null, 400);
        }
    }

    public function drivers()
    {
        try {
            $drivers = $this->service->getAvailableDrivers();

            return $this->successResponse($drivers, 'Drivers retrieved successfully');
        } catch (\Exception $e) {
            return $this->serverErrorResponse($e->getMessage());
        }
    }

    public function storeDriver()
    {
        try {
            $data = $this->request->all();
            $errors = $this->validateDriverData($data);

            if (! empty($errors)) {
                return $this->validationErrorResponse($errors);
            }

            $driver = $this->service->createDriver($data);

            return $this->successResponse($driver, 'Driver created successfully', 201);
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 'DRIVER_CREATION_ERROR', null, 400);
        }
    }

    public function createFee(string $registrationId)
    {
        try {
            $data = $this->request->all();

            $fee = $this->service->createFee($registrationId, $data);

            return $this->successResponse($fee, 'Fee created successfully', 201);
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 'FEE_CREATION_ERROR', null, 400);
        }
    }

    public function markFeePaid(string $feeId)
    {
        try {
            $data = $this->request->all();
            $fee = $this->service->markFeePaid($feeId, $data['payment_method'] ?? null, $data['transaction_reference'] ?? null);

            return $this->successResponse($fee, 'Fee marked as paid successfully');
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 'FEE_UPDATE_ERROR', null, 400);
        }
    }

    public function incidents()
    {
        try {
            $filters = $this->request->all();
            $incidents = \App\Models\Transportation\TransportationIncident::query();

            if (! empty($filters['status'])) {
                $incidents->where('status', $filters['status']);
            }

            return $this->successResponse($incidents->get()->toArray(), 'Incidents retrieved successfully');
        } catch (\Exception $e) {
            return $this->serverErrorResponse($e->getMessage());
        }
    }

    public function storeIncident()
    {
        try {
            $data = $this->request->all();
            $errors = $this->validateIncidentData($data);

            if (! empty($errors)) {
                return $this->validationErrorResponse($errors);
            }

            $incident = $this->service->createIncident($data);

            return $this->successResponse($incident, 'Incident reported successfully', 201);
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 'INCIDENT_CREATION_ERROR', null, 400);
        }
    }

    public function updateIncident(string $id)
    {
        try {
            $data = $this->request->all();
            $errors = $this->validateIncidentData($data);

            if (! empty($errors)) {
                return $this->validationErrorResponse($errors);
            }

            $incident = $this->service->updateIncident($id, $data);

            return $this->successResponse($incident, 'Incident updated successfully');
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 'INCIDENT_UPDATE_ERROR', null, 400);
        }
    }

    public function getRouteStatistics(string $id)
    {
        try {
            $statistics = $this->service->getRouteStatistics($id);

            return $this->successResponse($statistics, 'Route statistics retrieved successfully');
        } catch (\Exception $e) {
            return $this->serverErrorResponse($e->getMessage());
        }
    }

    private function validateRouteData(array $data): array
    {
        $errors = [];

        if (empty($data['route_name'])) {
            $errors['route_name'] = ['The route_name field is required.'];
        }

        if (empty($data['origin'])) {
            $errors['origin'] = ['The origin field is required.'];
        }

        if (empty($data['destination'])) {
            $errors['destination'] = ['The destination field is required.'];
        }

        return $errors;
    }

    private function validateRegistrationData(array $data): array
    {
        $errors = [];

        if (empty($data['expiry_date'])) {
            $errors['expiry_date'] = ['The expiry_date field is required.'];
        }

        if (isset($data['fee_amount']) && ! is_numeric($data['fee_amount'])) {
            $errors['fee_amount'] = ['The fee_amount must be a number.'];
        }

        return $errors;
    }

    private function validateAssignmentData(array $data): array
    {
        $errors = [];

        if (empty($data['driver_id']) && empty($data['vehicle_id'])) {
            $errors['assignment'] = ['Either driver_id or vehicle_id must be specified.'];
        }

        return $errors;
    }

    private function validateVehicleData(array $data): array
    {
        $errors = [];

        if (empty($data['vehicle_number'])) {
            $errors['vehicle_number'] = ['The vehicle_number field is required.'];
        }

        if (empty($data['license_plate'])) {
            $errors['license_plate'] = ['The license_plate field is required.'];
        }

        if (! is_numeric($data['capacity'] ?? null)) {
            $errors['capacity'] = ['The capacity must be a number.'];
        }

        return $errors;
    }

    private function validateDriverData(array $data): array
    {
        $errors = [];

        if (empty($data['driver_name'])) {
            $errors['driver_name'] = ['The driver_name field is required.'];
        }

        return $errors;
    }

    private function validateIncidentData(array $data): array
    {
        $errors = [];

        if (empty($data['incident_date'])) {
            $errors['incident_date'] = ['The incident_date field is required.'];
        }

        if (empty($data['incident_type'])) {
            $errors['incident_type'] = ['The incident_type field is required.'];
        }

        if (empty($data['severity'])) {
            $errors['severity'] = ['The severity field is required.'];
        }

        return $errors;
    }
}
