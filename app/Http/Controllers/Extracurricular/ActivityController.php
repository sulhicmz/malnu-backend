<?php

declare(strict_types=1);

namespace App\Http\Controllers\Extracurricular;

use App\Http\Controllers\Api\BaseController;
use App\Services\ClubManagementService;
use Hyperf\HttpServer\Contract\RequestInterface;
use Hyperf\HttpServer\Contract\ResponseInterface;
use Psr\Container\ContainerInterface;

class ActivityController extends BaseController
{
    private ClubManagementService $clubService;

    public function __construct(
        RequestInterface $request,
        ResponseInterface $response,
        ContainerInterface $container,
        ClubManagementService $clubService
    ) {
        parent::__construct($request, $response, $container);
        $this->clubService = $clubService;
    }

    public function index()
    {
        try {
            $filters = [];
            
            if ($this->request->input('club_id')) {
                $filters['club_id'] = $this->request->input('club_id');
            }
            
            if ($this->request->input('status')) {
                $filters['status'] = $this->request->input('status');
            }

            $activities = [];
            if (!empty($filters)) {
                $activities = $this->clubService->getClubActivities($filters['club_id'], $filters['status'] ?? null);
            } else {
                $activities = $this->clubService->getUpcomingActivities(50);
            }

            return $this->successResponse($activities, 'Activities retrieved successfully');
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 'ACTIVITY_RETRIEVAL_ERROR');
        }
    }

    public function store()
    {
        try {
            $data = $this->request->all();
            
            $errors = $this->validateActivityData($data);
            
            if (!empty($errors)) {
                return $this->validationErrorResponse($errors);
            }

            $activity = $this->clubService->createActivity($data['club_id'], $data);

            return $this->successResponse($activity, 'Activity created successfully', 201);
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 'ACTIVITY_CREATION_ERROR', null, 400);
        }
    }

    public function show(string $id)
    {
        try {
            $activity = $this->clubService->getActivity($id);

            return $this->successResponse($activity, 'Activity retrieved successfully');
        } catch (\Exception $e) {
            return $this->notFoundResponse('Activity not found');
        }
    }

    public function update(string $id)
    {
        try {
            $data = $this->request->all();
            
            $errors = $this->validateActivityData($data, true);
            
            if (!empty($errors)) {
                return $this->validationErrorResponse($errors);
            }

            $activity = $this->clubService->updateActivity($id, $data);

            return $this->successResponse($activity, 'Activity updated successfully');
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 'ACTIVITY_UPDATE_ERROR', null, 400);
        }
    }

    public function destroy(string $id)
    {
        try {
            $this->clubService->deleteActivity($id);

            return $this->successResponse(null, 'Activity deleted successfully');
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 'ACTIVITY_DELETION_ERROR', null, 400);
        }
    }

    public function attendances(string $id)
    {
        try {
            $attendance = $this->clubService->getActivityAttendance($id);

            return $this->successResponse($attendance, 'Activity attendance retrieved successfully');
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 'ATTENDANCE_RETRIEVAL_ERROR');
        }
    }

    public function markAttendance(string $id)
    {
        try {
            $data = $this->request->all();
            
            $errors = $this->validateAttendanceData($data);
            
            if (!empty($errors)) {
                return $this->validationErrorResponse($errors);
            }

            $attendance = $this->clubService->markAttendance(
                $id,
                $data['student_id'],
                $data['status'],
                $data['notes'] ?? null
            );

            return $this->successResponse($attendance, 'Attendance marked successfully', 201);
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 'ATTENDANCE_MARKING_ERROR', null, 400);
        }
    }

    public function statistics(string $id)
    {
        try {
            $stats = $this->clubService->getActivityAttendanceStatistics($id);

            return $this->successResponse($stats, 'Activity attendance statistics retrieved successfully');
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 'STATISTICS_ERROR');
        }
    }

    private function validateActivityData(array $data, bool $isUpdate = false): array
    {
        $errors = [];
        
        if (!$isUpdate && empty($data['name'])) {
            $errors['name'] = 'Activity name is required';
        }
        
        if (!$isUpdate && empty($data['club_id'])) {
            $errors['club_id'] = 'Club ID is required';
        }
        
        if (!$isUpdate && empty($data['start_date'])) {
            $errors['start_date'] = 'Start date is required';
        }
        
        if (isset($data['end_date']) && strtotime($data['end_date']) <= strtotime($data['start_date'])) {
            $errors['end_date'] = 'End date must be after start date';
        }
        
        if (isset($data['max_attendees']) && (!is_numeric($data['max_attendees']) || (int)$data['max_attendees'] < 1)) {
            $errors['max_attendees'] = 'Max attendees must be a positive number';
        }
        
        if (isset($data['status']) && !in_array($data['status'], ['scheduled', 'cancelled', 'completed'])) {
            $errors['status'] = 'Invalid status';
        }

        return $errors;
    }

    private function validateAttendanceData(array $data): array
    {
        $errors = [];
        
        if (empty($data['student_id'])) {
            $errors['student_id'] = 'Student ID is required';
        }
        
        if (empty($data['status'])) {
            $errors['status'] = 'Status is required';
        }
        
        if (!in_array($data['status'], ['present', 'absent', 'excused'])) {
            $errors['status'] = 'Invalid status. Must be present, absent, or excused';
        }

        return $errors;
    }
}
