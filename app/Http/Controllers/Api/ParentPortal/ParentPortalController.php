<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\ParentPortal;

use App\Http\Controllers\Api\BaseController;
use App\Services\ParentPortalService;
use Hyperf\HttpServer\Annotation\Controller;
use Hyperf\HttpServer\Annotation\GetMapping;
use Hyperf\HttpServer\Contract\RequestInterface;

#[Controller]
class ParentPortalController extends BaseController
{
    public function __construct(
        private readonly ParentPortalService $parentPortalService
    ) {}

    #[GetMapping('/api/parent/children')]
    public function getChildren(RequestInterface $request)
    {
        try {
            $userId = $this->getAuthenticatedUserId();
            $children = $this->parentPortalService->getParentChildren($userId);

            return $this->successResponse($children, 'Children retrieved successfully');
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 'RETRIEVAL_ERROR', null, 500);
        }
    }

    #[GetMapping('/api/parent/children/{studentId}/dashboard')]
    public function getStudentDashboard(RequestInterface $request)
    {
        try {
            $userId = $this->getAuthenticatedUserId();
            $studentId = $request->route('studentId');
            $term = $request->input('term');

            $dashboard = $this->parentPortalService->getStudentDashboard($userId, $studentId);

            return $this->successResponse($dashboard, 'Student dashboard retrieved successfully');
        } catch (\RuntimeException $e) {
            return $this->forbiddenResponse($e->getMessage());
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 'DASHBOARD_ERROR', null, 500);
        }
    }

    #[GetMapping('/api/parent/children/{studentId}/progress')]
    public function getStudentProgress(RequestInterface $request)
    {
        try {
            $userId = $this->getAuthenticatedUserId();
            $studentId = $request->route('studentId');
            $term = $request->input('term');

            $progress = $this->parentPortalService->getStudentProgress($userId, $studentId, $term);

            return $this->successResponse($progress, 'Student progress retrieved successfully');
        } catch (\RuntimeException $e) {
            return $this->forbiddenResponse($e->getMessage());
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 'PROGRESS_ERROR', null, 500);
        }
    }

    #[GetMapping('/api/parent/children/{studentId}/transcript')]
    public function getStudentTranscript(RequestInterface $request)
    {
        try {
            $userId = $this->getAuthenticatedUserId();
            $studentId = $request->route('studentId');

            $transcript = $this->parentPortalService->getStudentTranscript($userId, $studentId);

            return $this->successResponse($transcript, 'Student transcript retrieved successfully');
        } catch (\RuntimeException $e) {
            return $this->forbiddenResponse($e->getMessage());
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 'TRANSCRIPT_ERROR', null, 500);
        }
    }

    #[GetMapping('/api/parent/children/{studentId}/attendance')]
    public function getStudentAttendance(RequestInterface $request)
    {
        try {
            $userId = $this->getAuthenticatedUserId();
            $studentId = $request->route('studentId');
            $startDate = $request->input('start_date');
            $endDate = $request->input('end_date');

            $attendance = $this->parentPortalService->getStudentAttendance($userId, $studentId, $startDate, $endDate);

            return $this->successResponse($attendance, 'Student attendance retrieved successfully');
        } catch (\RuntimeException $e) {
            return $this->forbiddenResponse($e->getMessage());
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 'ATTENDANCE_ERROR', null, 500);
        }
    }

    #[GetMapping('/api/parent/children/{studentId}/assignments')]
    public function getStudentAssignments(RequestInterface $request)
    {
        try {
            $userId = $this->getAuthenticatedUserId();
            $studentId = $request->route('studentId');

            $assignments = $this->parentPortalService->getStudentAssignments($userId, $studentId);

            return $this->successResponse($assignments, 'Student assignments retrieved successfully');
        } catch (\RuntimeException $e) {
            return $this->forbiddenResponse($e->getMessage());
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 'ASSIGNMENTS_ERROR', null, 500);
        }
    }

    #[GetMapping('/api/parent/children/{studentId}/behavior')]
    public function getStudentBehavior(RequestInterface $request)
    {
        try {
            $userId = $this->getAuthenticatedUserId();
            $studentId = $request->route('studentId');

            $behavior = $this->parentPortalService->getStudentBehaviorRecords($userId, $studentId);

            return $this->successResponse($behavior, 'Student behavior records retrieved successfully');
        } catch (\RuntimeException $e) {
            return $this->forbiddenResponse($e->getMessage());
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 'BEHAVIOR_ERROR', null, 500);
        }
    }

    private function getAuthenticatedUserId(): string
    {
        $user = $this->request->getAttribute('user');
        if (!$user) {
            throw new \RuntimeException('User not authenticated');
        }
        return $user['id'];
    }
}
