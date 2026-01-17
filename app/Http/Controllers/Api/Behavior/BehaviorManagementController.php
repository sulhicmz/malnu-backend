<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Behavior;

use App\Http\Controllers\Api\BaseController;
use App\Services\BehaviorManagementService;
use Hyperf\HttpServer\Contract\RequestInterface;
use Hyperf\HttpServer\Contract\ResponseInterface;
use Psr\Container\ContainerInterface;
use Hyperf\Di\Annotation\Inject;
use OpenApi\Annotations as OA;

/**
 * @OA\Tag(
 *     name="Behavior",
 *     description="Behavior and discipline management endpoints"
 * )
 */
class BehaviorManagementController extends BaseController
{
    #[Inject]
    private BehaviorManagementService $behaviorService;

    public function __construct(
        RequestInterface $request,
        ResponseInterface $response,
        ContainerInterface $container
    ) {
        parent::__construct($request, $response, $container);
    }

    /**
     * @OA\Post(
     *     path="/api/behavior/incidents",
     *     tags={"Behavior"},
     *     summary="Report a behavior incident",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"student_id", "behavior_category_id", "severity", "description"},
     *             @OA\Property(property="student_id", type="string", example="uuid-here", description="Student ID"),
     *             @OA\Property(property="behavior_category_id", type="string", example="uuid-here", description="Behavior category ID"),
     *             @OA\Property(property="severity", type="string", enum={"minor", "moderate", "severe", "critical"}, example="moderate"),
     *             @OA\Property(property="incident_date", type="string", format="date", example="2026-01-17"),
     *             @OA\Property(property="incident_time", type="string", format="time", example="14:30:00"),
     *             @OA\Property(property="location", type="string", example="Classroom 3B"),
     *             @OA\Property(property="description", type="string", example="Student was disruptive during class"),
     *             @OA\Property(property="witnesses", type="string", example="John Doe, Jane Smith")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Incident created successfully"
     *     )
     * )
     */
    public function createIncident(): object
    {
        $data = $this->request->all();

        $requiredFields = ['student_id', 'behavior_category_id', 'severity', 'description'];
        foreach ($requiredFields as $field) {
            if (!isset($data[$field]) || empty($data[$field])) {
                return $this->errorResponse(
                    sprintf('Field %s is required', $field),
                    'VALIDATION_ERROR',
                    ['field' => $field]
                );
            }
        }

        $validSeverities = ['minor', 'moderate', 'severe', 'critical'];
        if (!in_array($data['severity'], $validSeverities)) {
            return $this->errorResponse(
                'Invalid severity level',
                'VALIDATION_ERROR',
                ['valid_values' => $validSeverities]
            );
        }

        $data['reported_by'] = $this->getCurrentUserId();
        $data['created_by'] = $this->getCurrentUserId();

        try {
            $incident = $this->behaviorService->createIncident($data);
            return $this->successResponse($incident, 'Incident reported successfully', 201);
        } catch (\Exception $e) {
            return $this->serverErrorResponse('Failed to create incident: ' . $e->getMessage());
        }
    }

    /**
     * @OA\Get(
     *     path="/api/behavior/incidents",
     *     tags={"Behavior"},
     *     summary="Get behavior incidents",
     *     @OA\Parameter(
     *         name="student_id",
     *         in="query",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="severity",
     *         in="query",
     *         required=false,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="start_date",
     *         in="query",
     *         required=false,
     *         @OA\Schema(type="string", format="date")
     *     ),
     *     @OA\Parameter(
     *         name="end_date",
     *         in="query",
     *         required=false,
     *         @OA\Schema(type="string", format="date")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Incidents retrieved successfully"
     *     )
     * )
     */
    public function getIncidents(): object
    {
        $studentId = $this->request->input('student_id');
        $severity = $this->request->input('severity');
        $startDate = $this->request->input('start_date');
        $endDate = $this->request->input('end_date');
        $page = (int) $this->request->input('page', 1);
        $perPage = (int) $this->request->input('per_page', 15);

        try {
            $query = $this->behaviorService->incidentModel->with('category', 'reportedBy');

            if ($studentId) {
                $query->where('student_id', $studentId);
            }

            if ($severity) {
                $query->where('severity', $severity);
            }

            if ($startDate && $endDate) {
                $query->whereBetween('incident_date', [$startDate, $endDate]);
            }

            $incidents = $query->orderBy('incident_date', 'desc')
                ->offset(($page - 1) * $perPage)
                ->limit($perPage)
                ->get();

            $total = $query->count();

            return $this->successResponse([
                'data' => $incidents,
                'pagination' => [
                    'current_page' => $page,
                    'per_page' => $perPage,
                    'total' => $total,
                    'last_page' => (int) ceil($total / $perPage),
                ],
            ]);
        } catch (\Exception $e) {
            return $this->serverErrorResponse('Failed to retrieve incidents: ' . $e->getMessage());
        }
    }

    /**
     * @OA\Post(
     *     path="/api/behavior/discipline-actions",
     *     tags={"Behavior"},
     *     summary="Create a disciplinary action",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"incident_id", "action_type"},
     *             @OA\Property(property="incident_id", type="string", example="uuid-here"),
     *             @OA\Property(property="action_type", type="string", enum={"warning", "detention", "suspension", "expulsion", "counseling", "community_service", "parent_conference", "other"}),
     *             @OA\Property(property="duration_days", type="integer", example=3),
     *             @OA\Property(property="start_date", type="string", format="date", example="2026-01-20"),
     *             @OA\Property(property="end_date", type="string", format="date", example="2026-01-23"),
     *             @OA\Property(property="description", type="string", example="Suspended for fighting"),
     *             @OA\Property(property="conditions", type="string", example="Complete counseling before returning")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Disciplinary action created successfully"
     *     )
     * )
     */
    public function createDisciplineAction(): object
    {
        $data = $this->request->all();

        $requiredFields = ['incident_id', 'action_type'];
        foreach ($requiredFields as $field) {
            if (!isset($data[$field]) || empty($data[$field])) {
                return $this->errorResponse(
                    sprintf('Field %s is required', $field),
                    'VALIDATION_ERROR',
                    ['field' => $field]
                );
            }
        }

        $validActionTypes = ['warning', 'detention', 'suspension', 'expulsion', 'counseling', 'community_service', 'parent_conference', 'other'];
        if (!in_array($data['action_type'], $validActionTypes)) {
            return $this->errorResponse(
                'Invalid action type',
                'VALIDATION_ERROR',
                ['valid_values' => $validActionTypes]
            );
        }

        $data['created_by'] = $this->getCurrentUserId();
        $data['assigned_to'] = $this->request->input('assigned_to');

        try {
            $action = $this->behaviorService->createDisciplineAction($data);
            return $this->successResponse($action, 'Disciplinary action created successfully', 201);
        } catch (\Exception $e) {
            return $this->serverErrorResponse('Failed to create disciplinary action: ' . $e->getMessage());
        }
    }

    /**
     * @OA\Get(
     *     path="/api/behavior/student/{id}/history",
     *     tags={"Behavior"},
     *     summary="Get student behavior history",
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Student behavior history retrieved successfully"
     *     )
     * )
     */
    public function getStudentHistory(string $id): object
    {
        try {
            $history = $this->behaviorService->getStudentBehaviorHistory($id);
            return $this->successResponse($history);
        } catch (\Exception $e) {
            return $this->serverErrorResponse('Failed to retrieve student behavior history: ' . $e->getMessage());
        }
    }

    /**
     * @OA\Post(
     *     path="/api/behavior/intervention-plans",
     *     tags={"Behavior"},
     *     summary="Create an intervention plan",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"student_id", "title", "description"},
     *             @OA\Property(property="student_id", type="string", example="uuid-here"),
     *             @OA\Property(property="title", type="string", example="Behavioral Support Plan"),
     *             @OA\Property(property="description", type="string", example="Weekly counseling sessions for 3 months"),
     *             @OA\Property(property="goals", type="string", example="Improve classroom behavior"),
     *             @OA\Property(property="strategies", type="string", example="Positive reinforcement, clear expectations"),
     *             @OA\Property(property="assigned_to", type="string", example="uuid-here"),
     *             @OA\Property(property="start_date", type="string", format="date", example="2026-01-20"),
     *             @OA\Property(property="end_date", type="string", format="date", example="2026-04-20")
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Intervention plan created successfully"
     *     )
     * )
     */
    public function createInterventionPlan(): object
    {
        $data = $this->request->all();

        $requiredFields = ['student_id', 'title', 'description'];
        foreach ($requiredFields as $field) {
            if (!isset($data[$field]) || empty($data[$field])) {
                return $this->errorResponse(
                    sprintf('Field %s is required', $field),
                    'VALIDATION_ERROR',
                    ['field' => $field]
                );
            }
        }

        $data['created_by'] = $this->getCurrentUserId();
        $data['assigned_to'] = $this->request->input('assigned_to');

        try {
            $plan = $this->behaviorService->createInterventionPlan($data);
            return $this->successResponse($plan, 'Intervention plan created successfully', 201);
        } catch (\Exception $e) {
            return $this->serverErrorResponse('Failed to create intervention plan: ' . $e->getMessage());
        }
    }

    /**
     * @OA\Get(
     *     path="/api/behavior/reports",
     *     tags={"Behavior"},
     *     summary="Generate behavior reports",
     *     @OA\Parameter(
     *         name="start_date",
     *         in="query",
     *         required=false,
     *         @OA\Schema(type="string", format="date")
     *     ),
     *     @OA\Parameter(
     *         name="end_date",
     *         in="query",
     *         required=false,
     *         @OA\Schema(type="string", format="date")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Behavior report generated successfully"
     *     )
     * )
     */
    public function getReports(): object
    {
        $filters = [
            'start_date' => $this->request->input('start_date'),
            'end_date' => $this->request->input('end_date'),
            'severity' => $this->request->input('severity'),
            'category_id' => $this->request->input('category_id'),
        ];

        try {
            $statistics = $this->behaviorService->getBehaviorStatistics($filters);
            return $this->successResponse($statistics);
        } catch (\Exception $e) {
            return $this->serverErrorResponse('Failed to generate behavior report: ' . $e->getMessage());
        }
    }

    private function getCurrentUserId(): ?string
    {
        return $this->request->getAttribute('user_id');
    }
}
