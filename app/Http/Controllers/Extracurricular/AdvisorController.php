<?php

declare(strict_types=1);

namespace App\Http\Controllers\Extracurricular;

use App\Http\Controllers\Api\BaseController;
use App\Services\ClubManagementService;
use Hyperf\HttpServer\Contract\RequestInterface;
use Hyperf\HttpServer\Contract\ResponseInterface;
use Psr\Container\ContainerInterface;

class AdvisorController extends BaseController
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
            
            if ($this->request->input('teacher_id')) {
                $filters['teacher_id'] = $this->request->input('teacher_id');
            }

            $advisors = $this->clubService->getClubAdvisors($filters['club_id'] ?? '');

            if (!empty($filters['teacher_id'])) {
                $advisors = $advisors->filter(fn($a) => $a->teacher_id === $filters['teacher_id'])->values();
            }

            return $this->successResponse($advisors, 'Club advisors retrieved successfully');
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 'ADVISOR_RETRIEVAL_ERROR');
        }
    }

    public function store()
    {
        try {
            $data = $this->request->all();
            
            $errors = $this->validateAdvisorData($data);
            
            if (!empty($errors)) {
                return $this->validationErrorResponse($errors);
            }

            $advisor = $this->clubService->assignAdvisor(
                $data['club_id'],
                $data['teacher_id'],
                $data['notes'] ?? null
            );

            return $this->successResponse($advisor, 'Advisor assigned successfully', 201);
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 'ADVISOR_ASSIGNMENT_ERROR', null, 400);
        }
    }

    public function show(string $id)
    {
        try {
            $data = $this->request->all();
            
            $filters = [];
            if ($this->request->input('club_id')) {
                $filters['club_id'] = $this->request->input('club_id');
            }

            $advisors = $this->clubService->getClubAdvisors($filters['club_id'] ?? '');
            $advisor = $advisors->first(fn($a) => $a->id === $id);

            if (!$advisor) {
                return $this->notFoundResponse('Advisor not found');
            }

            return $this->successResponse($advisor, 'Advisor retrieved successfully');
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 'ADVISOR_RETRIEVAL_ERROR');
        }
    }

    public function update(string $id)
    {
        try {
            $data = $this->request->all();
            
            $errors = $this->validateAdvisorData($data, true);
            
            if (!empty($errors)) {
                return $this->validationErrorResponse($errors);
            }

            $advisor = $this->clubService->assignAdvisor(
                $data['club_id'],
                $id,
                $data['notes'] ?? null
            );

            return $this->successResponse($advisor, 'Advisor updated successfully');
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 'ADVISOR_UPDATE_ERROR', null, 400);
        }
    }

    public function destroy(string $id)
    {
        try {
            $data = $this->request->all();
            
            $clubId = $data['club_id'] ?? '';
            $teacherId = $this->request->input('teacher_id');
            
            $result = $this->clubService->removeAdvisor($clubId, $teacherId);

            if (!$result) {
                return $this->notFoundResponse('Advisor assignment not found');
            }

            return $this->successResponse(null, 'Advisor removed successfully');
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 'ADVISOR_DELETION_ERROR', null, 400);
        }
    }

    public function teacher(string $teacherId)
    {
        try {
            $advisories = $this->clubService->getTeacherAdvisories($teacherId);

            return $this->successResponse($advisories, 'Teacher advisories retrieved successfully');
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 'TEACHER_ADVISORIES_ERROR');
        }
    }

    private function validateAdvisorData(array $data, bool $isUpdate = false): array
    {
        $errors = [];
        
        if (!$isUpdate && empty($data['club_id'])) {
            $errors['club_id'] = 'Club ID is required';
        }
        
        if (!$isUpdate && empty($data['teacher_id'])) {
            $errors['teacher_id'] = 'Teacher ID is required';
        }

        return $errors;
    }
}
