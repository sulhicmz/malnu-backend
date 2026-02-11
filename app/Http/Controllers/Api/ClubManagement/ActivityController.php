<?php

declare (strict_types = 1);

namespace App\Http\Controllers\Api\ClubManagement;

use App\Http\Controllers\Api\BaseController;
use App\Services\ClubManagementService;
use Psr\Http\Message\ServerRequestInterface;

class ActivityController extends BaseController
{
    private ClubManagementService $clubManagementService;

    public function __construct(ClubManagementService $clubManagementService)
    {
        parent::__construct(
            $this->request,
            $this->response,
            $this->container
        );
        $this->clubManagementService = $clubManagementService;
    }

    public function index()
    {
        $activities = \App\Models\ClubManagement\Activity::with(['club', 'club.advisor', 'attendances', 'attendances.student'])->get();
        return $this->successResponse($activities);
    }

    public function store(ServerRequestInterface $request)
    {
        $data = $request->all();
        $validationErrors = $this->validateActivityData($data);

        if (!empty($validationErrors)) {
            return $this->validationErrorResponse($validationErrors);
        }

        try {
            $activity = $this->clubManagementService->createActivity($data['club_id'], $data);
            return $this->successResponse($activity, 'Activity created successfully', 201);
        } catch (\Exception $e) {
            return $this->serverErrorResponse('Failed to create activity: ' . $e->getMessage());
        }
    }

    public function show(string $id)
    {
        $activity = \App\Models\ClubManagement\Activity::with(['club', 'club.advisor', 'attendances', 'attendances.student'])->find($id);

        if (!$activity) {
            return $this->notFoundResponse('Activity not found');
        }

        return $this->successResponse($activity);
    }

    public function update(ServerRequestInterface $request, string $id)
    {
        $activity = \App\Models\ClubManagement\Activity::find($id);

        if (!$activity) {
            return $this->notFoundResponse('Activity not found');
        }

        $data = $request->all();
        $validationErrors = $this->validateActivityData($data, true);

        if (!empty($validationErrors)) {
            return $this->validationErrorResponse($validationErrors);
        }

        try {
            $updatedActivity = $this->clubManagementService->updateActivity($id, $data);
            return $this->successResponse($updatedActivity, 'Activity updated successfully');
        } catch (\Exception $e) {
            return $this->serverErrorResponse('Failed to update activity: ' . $e->getMessage());
        }
    }

    public function destroy(string $id)
    {
        $activity = \App\Models\ClubManagement\Activity::find($id);

        if (!$activity) {
            return $this->notFoundResponse('Activity not found');
        }

        try {
            $deleted = $this->clubManagementService->deleteActivity($id);
            if ($deleted) {
                return $this->successResponse(null, 'Activity deleted successfully');
            } else {
                return $this->serverErrorResponse('Failed to delete activity');
            }
        } catch (\Exception $e) {
            return $this->serverErrorResponse('Failed to delete activity: ' . $e->getMessage());
        }
    }

    private function validateActivityData(array $data, bool $isUpdate = false): array
    {
        $errors = [];

        if (!$isUpdate && empty($data['club_id'] ?? '')) {
            $errors['club_id'] = 'Club ID is required';
        }

        if (!$isUpdate && empty($data['name'] ?? '')) {
            $errors['name'] = 'Activity name is required';
        }

        if (isset($data['name']) && strlen($data['name']) > 100) {
            $errors['name'] = 'Activity name must not exceed 100 characters';
        }

        if (!$isUpdate && empty($data['start_date'] ?? '')) {
            $errors['start_date'] = 'Start date is required';
        }

        if (isset($data['max_attendees']) && (!is_int($data['max_attendees']) || $data['max_attendees'] < 0)) {
            $errors['max_attendees'] = 'Max attendees must be a positive integer';
        }

        if (isset($data['club_id']) && !\App\Models\ClubManagement\Club::find($data['club_id'])) {
            $errors['club_id'] = 'Club not found';
        }

        return $errors;
    }
}
