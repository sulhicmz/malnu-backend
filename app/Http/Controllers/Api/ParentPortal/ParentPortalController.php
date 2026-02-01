<?php

namespace App\Http\Controllers\Api\ParentPortal;

use App\Http\Controllers\Api\BaseController;
use App\Services\ParentPortalService;
use Hyperf\HttpServer\Contract\RequestInterface;
use Hyperf\HttpServer\Contract\ResponseInterface;
use OpenApi\Annotations as OA;
use Psr\Container\ContainerInterface;

/**
 * @OA\Tag(
 *     name="Parent Portal",
 *     description="Parent portal endpoints for tracking student progress"
 * )
 */
class ParentPortalController extends BaseController
{
    private ParentPortalService $parentPortalService;

    public function __construct(
        RequestInterface $request,
        ResponseInterface $response,
        ContainerInterface $container,
        ParentPortalService $parentPortalService
    ) {
        parent::__construct($request, $response, $container);
        $this->parentPortalService = $parentPortalService;
    }

    /**
     * Get parent dashboard with overview of all children.
     *
     * @OA\Get(
     *     path="/api/parent/dashboard",
     *     summary="Get parent dashboard",
     *     description="Retrieve overview of all children with latest grades and attendance summary",
     *     tags={"Parent Portal"},
     *     @OA\Response(
     *         response=200,
     *         description="Dashboard retrieved successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="parent_info", type="object"),
     *                 @OA\Property(property="children_count", type="integer"),
     *                 @OA\Property(property="children", type="array", @OA\Items(type="object"))
     *             )
     *         )
     *     ),
     *     @OA\Response(response=401, description="Unauthorized")
     * )
     */
    public function dashboard()
    {
        try {
            $parentId = $this->getAuthenticatedUserId();

            $dashboardData = $this->parentPortalService->getDashboard($parentId);

            return $this->successResponse($dashboardData, 'Dashboard retrieved successfully');
        } catch (\Exception $e) {
            return $this->serverErrorResponse($e->getMessage());
        }
    }

    /**
     * Get detailed grade report for a specific child.
     *
     * @OA\Get(
     *     path="/api/parent/children/{id}/grades",
     *     summary="Get child grades",
     *     description="Retrieve detailed grade report organized by subject for a specific child",
     *     tags={"Parent Portal"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="Student ID",
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Grades retrieved successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="student", type="object"),
     *                 @OA\Property(property="grades_by_subject", type="array"),
     *                 @OA\Property(property="total_grades", type="integer")
     *             )
     *         )
     *     ),
     *     @OA\Response(response=403, description="Forbidden - Access denied"),
     *     @OA\Response(response=404, description="Student not found")
     * )
     */
    public function getChildGrades(string $id)
    {
        try {
            $parentId = $this->getAuthenticatedUserId();

            $gradesData = $this->parentPortalService->getChildGrades($parentId, $id);

            return $this->successResponse($gradesData, 'Grades retrieved successfully');
        } catch (\Exception $e) {
            if (str_contains($e->getMessage(), 'Access denied')) {
                return $this->forbiddenResponse($e->getMessage());
            }
            return $this->serverErrorResponse($e->getMessage());
        }
    }

    /**
     * Get attendance records for a specific child.
     *
     * @OA\Get(
     *     path="/api/parent/children/{id}/attendance",
     *     summary="Get child attendance",
     *     description="Retrieve attendance records with optional date range filtering",
     *     tags={"Parent Portal"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="Student ID",
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="start_date",
     *         in="query",
     *         required=false,
     *         description="Start date (Y-m-d format)",
     *         @OA\Schema(type="string", format="date", example="2024-01-01")
     *     ),
     *     @OA\Parameter(
     *         name="end_date",
     *         in="query",
     *         required=false,
     *         description="End date (Y-m-d format)",
     *         @OA\Schema(type="string", format="date", example="2024-12-31")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Attendance retrieved successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="student", type="object"),
     *                 @OA\Property(property="summary", type="object"),
     *                 @OA\Property(property="records", type="array")
     *             )
     *         )
     *     ),
     *     @OA\Response(response=403, description="Forbidden - Access denied")
     * )
     */
    public function getChildAttendance(string $id)
    {
        try {
            $parentId = $this->getAuthenticatedUserId();

            $startDate = $this->request->query('start_date');
            $endDate = $this->request->query('end_date');

            $attendanceData = $this->parentPortalService->getChildAttendance($parentId, $id, $startDate, $endDate);

            return $this->successResponse($attendanceData, 'Attendance retrieved successfully');
        } catch (\Exception $e) {
            if (str_contains($e->getMessage(), 'Access denied')) {
                return $this->forbiddenResponse($e->getMessage());
            }
            return $this->serverErrorResponse($e->getMessage());
        }
    }

    /**
     * Get current and upcoming assignments for a specific child.
     *
     * @OA\Get(
     *     path="/api/parent/children/{id}/assignments",
     *     summary="Get child assignments",
     *     description="Retrieve current and upcoming assignments for a specific child",
     *     tags={"Parent Portal"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="Student ID",
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Assignments retrieved successfully",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="student", type="object"),
     *                 @OA\Property(property="upcoming_assignments", type="array"),
     *                 @OA\Property(property="past_assignments", type="array"),
     *                 @OA\Property(property="total_assignments", type="integer")
     *             )
     *         )
     *     ),
     *     @OA\Response(response=403, description="Forbidden - Access denied")
     * )
     */
    public function getChildAssignments(string $id)
    {
        try {
            $parentId = $this->getAuthenticatedUserId();

            $assignmentsData = $this->parentPortalService->getChildAssignments($parentId, $id);

            return $this->successResponse($assignmentsData, 'Assignments retrieved successfully');
        } catch (\Exception $e) {
            if (str_contains($e->getMessage(), 'Access denied')) {
                return $this->forbiddenResponse($e->getMessage());
            }
            return $this->serverErrorResponse($e->getMessage());
        }
    }

    /**
     * Get authenticated user ID from JWT token.
     */
    private function getAuthenticatedUserId(): int
    {
        $user = $this->request->getAttribute('user');

        if (!$user) {
            throw new \Exception('Unauthorized access');
        }

        return $user['id'];
    }
}
