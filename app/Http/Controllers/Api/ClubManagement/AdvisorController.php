<?php

declare (strict_types = 1);

namespace App\Http\Controllers\Api\ClubManagement;

use App\Http\Controllers\Api\BaseController;
use App\Services\ClubManagementService;
use Psr\Http\Message\ServerRequestInterface;

class AdvisorController extends BaseController
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
        $advisors = \App\Models\ClubManagement\ClubAdvisor::with(['club', 'teacher', 'teacher.user'])->get();
        return $this->successResponse($advisors);
    }

    public function store(ServerRequestInterface $request)
    {
        $data = $request->all();
        $validationErrors = $this->validateAdvisorData($data);

        if (!empty($validationErrors)) {
            return $this->validationErrorResponse($validationErrors);
        }

        try {
            $advisor = $this->clubManagementService->assignAdvisor($data['club_id'], $data['teacher_id']);
            return $this->successResponse($advisor, 'Advisor assigned successfully', 201);
        } catch (\Exception $e) {
            return $this->serverErrorResponse('Failed to assign advisor: ' . $e->getMessage());
        }
    }

    public function show(string $id)
    {
        $advisor = \App\Models\ClubManagement\ClubAdvisor::with(['club', 'teacher', 'teacher.user'])->find($id);

        if (!$advisor) {
            return $this->notFoundResponse('Advisor not found');
        }

        return $this->successResponse($advisor);
    }

    public function destroy(string $id)
    {
        $advisor = \App\Models\ClubManagement\ClubAdvisor::find($id);

        if (!$advisor) {
            return $this->notFoundResponse('Advisor not found');
        }

        try {
            $deleted = $this->clubManagementService->removeAdvisor($advisor->club_id, $advisor->teacher_id);
            if ($deleted) {
                return $this->successResponse(null, 'Advisor removed successfully');
            } else {
                return $this->serverErrorResponse('Failed to remove advisor');
            }
        } catch (\Exception $e) {
            return $this->serverErrorResponse('Failed to remove advisor: ' . $e->getMessage());
        }
    }

    private function validateAdvisorData(array $data): array
    {
        $errors = [];

        if (empty($data['club_id'] ?? '')) {
            $errors['club_id'] = 'Club ID is required';
        }

        if (empty($data['teacher_id'] ?? '')) {
            $errors['teacher_id'] = 'Teacher ID is required';
        }

        if (isset($data['club_id']) && !\App\Models\ClubManagement\Club::find($data['club_id'])) {
            $errors['club_id'] = 'Club not found';
        }

        if (isset($data['teacher_id']) && !\App\Models\SchoolManagement\Teacher::find($data['teacher_id'])) {
            $errors['teacher_id'] = 'Teacher not found';
        }

        return $errors;
    }
}
