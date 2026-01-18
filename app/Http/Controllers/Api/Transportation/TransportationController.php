<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Transportation;

use App\Http\Controllers\Api\BaseController;
use App\Services\TransportationManagementService;
use Hyperf\HttpServer\Contract\RequestInterface;
use Hyperf\HttpServer\Contract\ResponseInterface;
use Psr\Container\ContainerInterface;
use OpenApi\Annotations as OA;

/**
 * @OA\Tag(
 *     name="Transportation",
 *     description="Transportation management endpoints"
 * )
 */
class TransportationController extends BaseController
{
    private TransportationManagementService $transportationService;

    public function __construct(
        RequestInterface $request,
        ResponseInterface $response,
        ContainerInterface $container,
        TransportationManagementService $transportationService
    ) {
        parent::__construct($request, $response, $container);
        $this->transportationService = $transportationService;
    }

    /**
     * @OA\Post(
     *     path="/api/transportation/routes",
     *     tags={"Transportation"},
     *     summary="Create a new route",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(ref="#/components/schemas/TransportationRoute")
     *     ),
     *     @OA\Response(response=200, description="Route created successfully"),
     *     @OA\Response(response=422, description="Validation error")
     * )
     */
    public function createRoute()
    {
        $data = $this->request->all();
        $userId = $this->getUserId();

        $data['created_by'] = $userId;
        $data['updated_by'] = $userId;

        $route = $this->transportationService->createRoute($data);

        return $this->successResponse($route, 'Route created successfully');
    }

    /**
     * @OA\Put(
     *     path="/api/transportation/routes/{id}",
     *     tags={"Transportation"},
     *     summary="Update a route",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(ref="#/components/schemas/TransportationRoute")
     *     ),
     *     @OA\Response(response=200, description="Route updated successfully"),
     *     @OA\Response(response=404, description="Route not found")
     * )
     */
    public function updateRoute(string $id)
    {
        $data = $this->request->all();
        $userId = $this->getUserId();

        $data['updated_by'] = $userId;

        $route = $this->transportationService->updateRoute($id, $data);

        return $this->successResponse($route, 'Route updated successfully');
    }

    /**
     * @OA\Delete(
     *     path="/api/transportation/routes/{id}",
     *     tags={"Transportation"},
     *     summary="Delete a route",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(response=200, description="Route deleted successfully"),
     *     @OA\Response(response=404, description="Route not found")
     * )
     */
    public function deleteRoute(string $id)
    {
        $this->transportationService->deleteRoute($id);

        return $this->successResponse(null, 'Route deleted successfully');
    }

    /**
     * @OA\Post(
     *     path="/api/transportation/registrations",
     *     tags={"Transportation"},
     *     summary="Create a new registration",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(ref="#/components/schemas/TransportationRegistration")
     *     ),
     *     @OA\Response(response=200, description="Registration created successfully"),
     *     @OA\Response(response=422, description="Validation error")
     * )
     */
    public function createRegistration()
    {
        $data = $this->request->all();
        $userId = $this->getUserId();

        $data['created_by'] = $userId;
        $data['updated_by'] = $userId;

        $registration = $this->transportationService->createRegistration($data);

        return $this->successResponse($registration, 'Registration created successfully');
    }

    /**
     * @OA\Put(
     *     path="/api/transportation/registrations/{id}",
     *     tags={"Transportation"},
     *     summary="Update a registration",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(ref="#/components/schemas/TransportationRegistration")
     *     ),
     *     @OA\Response(response=200, description="Registration updated successfully"),
     *     @OA\Response(response=404, description="Registration not found")
     * )
     */
    public function updateRegistration(string $id)
    {
        $data = $this->request->all();
        $userId = $this->getUserId();

        $data['updated_by'] = $userId;

        $registration = $this->transportationService->updateRegistration($id, $data);

        return $this->successResponse($registration, 'Registration updated successfully');
    }

    /**
     * @OA\Post(
     *     path="/api/transportation/vehicles",
     *     tags={"Transportation"},
     *     summary="Create a new vehicle",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(ref="#/components/schemas/TransportationVehicle")
     *     ),
     *     @OA\Response(response=200, description="Vehicle created successfully"),
     *     @OA\Response(response=422, description="Validation error")
     * )
     */
    public function createVehicle()
    {
        $data = $this->request->all();
        $userId = $this->getUserId();

        $data['created_by'] = $userId;
        $data['updated_by'] = $userId;

        $vehicle = $this->transportationService->createVehicle($data);

        return $this->successResponse($vehicle, 'Vehicle created successfully');
    }

    /**
     * @OA\Put(
     *     path="/api/transportation/vehicles/{id}",
     *     tags={"Transportation"},
     *     summary="Update a vehicle",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(ref="#/components/schemas/TransportationVehicle")
     *     ),
     *     @OA\Response(response=200, description="Vehicle updated successfully"),
     *     @OA\Response(response=404, description="Vehicle not found")
     * )
     */
    public function updateVehicle(string $id)
    {
        $data = $this->request->all();
        $userId = $this->getUserId();

        $data['updated_by'] = $userId;

        $vehicle = $this->transportationService->updateVehicle($id, $data);

        return $this->successResponse($vehicle, 'Vehicle updated successfully');
    }

    /**
     * @OA\Post(
     *     path="/api/transportation/drivers",
     *     tags={"Transportation"},
     *     summary="Create a new driver",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(ref="#/components/schemas/TransportationDriver")
     *     ),
     *     @OA\Response(response=200, description="Driver created successfully"),
     *     @OA\Response(response=422, description="Validation error")
     * )
     */
    public function createDriver()
    {
        $data = $this->request->all();
        $userId = $this->getUserId();

        $data['created_by'] = $userId;
        $data['updated_by'] = $userId;

        $driver = $this->transportationService->createDriver($data);

        return $this->successResponse($driver, 'Driver created successfully');
    }

    /**
     * @OA\Put(
     *     path="/api/transportation/drivers/{id}",
     *     tags={"Transportation"},
     *     summary="Update a driver",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(ref="#/components/schemas/TransportationDriver")
     *     ),
     *     @OA\Response(response=200, description="Driver updated successfully"),
     *     @OA\Response(response=404, description="Driver not found")
     * )
     */
    public function updateDriver(string $id)
    {
        $data = $this->request->all();
        $userId = $this->getUserId();

        $data['updated_by'] = $userId;

        $driver = $this->transportationService->updateDriver($id, $data);

        return $this->successResponse($driver, 'Driver updated successfully');
    }

    /**
     * @OA\Post(
     *     path="/api/transportation/assign-student",
     *     tags={"Transportation"},
     *     summary="Assign student to route",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             @OA\Property(property="student_id", type="string"),
     *             @OA\Property(property="route_id", type="string"),
     *             @OA\Property(property="stop_id", type="string", nullable=true),
     *             @OA\Property(property="notes", type="string", nullable=true)
     *         )
     *     ),
     *     @OA\Response(response=200, description="Student assigned successfully"),
     *     @OA\Response(response=422, description="Validation error")
     * )
     */
    public function assignStudent()
    {
        $data = $this->request->all();
        $userId = $this->getUserId();

        $data['created_by'] = $userId;
        $data['updated_by'] = $userId;

        $assignment = $this->transportationService->assignStudentToRoute(
            $data['student_id'],
            $data['route_id'],
            $data
        );

        return $this->successResponse($assignment, 'Student assigned to route successfully');
    }

    /**
     * @OA\Post(
     *     path="/api/transportation/fees",
     *     tags={"Transportation"},
     *     summary="Create a new fee",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(ref="#/components/schemas/TransportationFee")
     *     ),
     *     @OA\Response(response=200, description="Fee created successfully"),
     *     @OA\Response(response=422, description="Validation error")
     * )
     */
    public function createFee()
    {
        $data = $this->request->all();
        $userId = $this->getUserId();

        $data['created_by'] = $userId;
        $data['updated_by'] = $userId;

        $fee = $this->transportationService->createFee($data);

        return $this->successResponse($fee, 'Fee created successfully');
    }

    /**
     * @OA\Put(
     *     path="/api/transportation/fees/{id}",
     *     tags={"Transportation"},
     *     summary="Update a fee",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(ref="#/components/schemas/TransportationFee")
     *     ),
     *     @OA\Response(response=200, description="Fee updated successfully"),
     *     @OA\Response(response=404, description="Fee not found")
     * )
     */
    public function updateFee(string $id)
    {
        $data = $this->request->all();
        $userId = $this->getUserId();

        $data['updated_by'] = $userId;

        $fee = $this->transportationService->updateFee($id, $data);

        return $this->successResponse($fee, 'Fee updated successfully');
    }

    /**
     * @OA\Post(
     *     path="/api/transportation/incidents",
     *     tags={"Transportation"},
     *     summary="Create a new incident",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(ref="#/components/schemas/TransportationIncident")
     *     ),
     *     @OA\Response(response=200, description="Incident created successfully"),
     *     @OA\Response(response=422, description="Validation error")
     * )
     */
    public function createIncident()
    {
        $data = $this->request->all();
        $userId = $this->getUserId();

        $data['created_by'] = $userId;
        $data['updated_by'] = $userId;

        $incident = $this->transportationService->createIncident($data);

        return $this->successResponse($incident, 'Incident created successfully');
    }

    /**
     * @OA\Put(
     *     path="/api/transportation/incidents/{id}",
     *     tags={"Transportation"},
     *     summary="Update an incident",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(ref="#/components/schemas/TransportationIncident")
     *     ),
     *     @OA\Response(response=200, description="Incident updated successfully"),
     *     @OA\Response(response=404, description="Incident not found")
     * )
     */
    public function updateIncident(string $id)
    {
        $data = $this->request->all();
        $userId = $this->getUserId();

        $data['updated_by'] = $userId;

        $incident = $this->transportationService->updateIncident($id, $data);

        return $this->successResponse($incident, 'Incident updated successfully');
    }
}
