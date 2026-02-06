<?php

declare (strict_types = 1);

namespace App\Http\Controllers\Api\ClubManagement;

use App\Http\Controllers\Api\BaseController;
use App\Services\ClubManagementService;
use Psr\Http\Message\ServerRequestInterface;

class ClubController extends BaseController
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
        $clubs = \App\Models\ClubManagement\Club::with(['advisor', 'advisor.user'])->get();
        return $this->successResponse($clubs);
    }

    public function store(ServerRequestInterface $request)
    {
        $data = $request->all();
        $validationErrors = $this->validateClubData($data);

        if (!empty($validationErrors)) {
            return $this->validationErrorResponse($validationErrors);
        }

        try {
            $club = $this->clubManagementService->createClub($data);
            return $this->successResponse($club, 'Club created successfully', 201);
        } catch (\Exception $e) {
            return $this->serverErrorResponse('Failed to create club: ' . $e->getMessage());
        }
    }

    public function show(string $id)
    {
        $club = \App\Models\ClubManagement\Club::with(['advisor', 'advisor.user', 'memberships', 'memberships.student', 'memberships.student.user'])->find($id);

        if (!$club) {
            return $this->notFoundResponse('Club not found');
        }

        return $this->successResponse($club);
    }

    public function update(ServerRequestInterface $request, string $id)
    {
        $club = \App\Models\ClubManagement\Club::find($id);

        if (!$club) {
            return $this->notFoundResponse('Club not found');
        }

        $data = $request->all();
        $validationErrors = $this->validateClubData($data, true);

        if (!empty($validationErrors)) {
            return $this->validationErrorResponse($validationErrors);
        }

        try {
            $updatedClub = $this->clubManagementService->updateClub($id, $data);
            return $this->successResponse($updatedClub, 'Club updated successfully');
        } catch (\Exception $e) {
            return $this->serverErrorResponse('Failed to update club: ' . $e->getMessage());
        }
    }

    public function destroy(string $id)
    {
        $club = \App\Models\ClubManagement\Club::find($id);

        if (!$club) {
            return $this->notFoundResponse('Club not found');
        }

        try {
            $deleted = $this->clubManagementService->deleteClub($id);
            if ($deleted) {
                return $this->successResponse(null, 'Club deleted successfully');
            } else {
                return $this->serverErrorResponse('Failed to delete club');
            }
        } catch (\Exception $e) {
            return $this->serverErrorResponse('Failed to delete club: ' . $e->getMessage());
        }
    }

    private function validateClubData(array $data, bool $isUpdate = false): array
    {
        $errors = [];

        if (!$isUpdate && empty($data['name'] ?? '')) {
            $errors['name'] = 'Club name is required';
        }

        if (isset($data['name']) && strlen($data['name']) > 100) {
            $errors['name'] = 'Club name must not exceed 100 characters';
        }

        if (isset($data['category']) && !in_array($data['category'], ['academic', 'sports', 'arts', 'community_service', 'other'])) {
            $errors['category'] = 'Invalid club category';
        }

        if (isset($data['max_members']) && (!is_int($data['max_members']) || $data['max_members'] < 0)) {
            $errors['max_members'] = 'Max members must be a positive integer';
        }

        if (isset($data['advisor_id']) && !\App\Models\SchoolManagement\Teacher::find($data['advisor_id'])) {
            $errors['advisor_id'] = 'Advisor not found';
        }

        return $errors;
    }
}
