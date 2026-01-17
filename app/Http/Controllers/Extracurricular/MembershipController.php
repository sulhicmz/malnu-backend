<?php

declare(strict_types=1);

namespace App\Http\Controllers\Extracurricular;

use App\Http\Controllers\Api\BaseController;
use App\Services\ClubManagementService;
use Hyperf\HttpServer\Contract\RequestInterface;
use Hyperf\HttpServer\Contract\ResponseInterface;
use Psr\Container\ContainerInterface;

class MembershipController extends BaseController
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
            
            if ($this->request->input('role')) {
                $filters['role'] = $this->request->input('role');
            }

            $memberships = $this->clubService->getClubMembers($filters['club_id'] ?? '');

            if (!empty($filters['role'])) {
                $memberships = $memberships->filter(fn($m) => $m->role === $filters['role'])->values();
            }

            return $this->successResponse($memberships, 'Club memberships retrieved successfully');
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 'MEMBERSHIP_RETRIEVAL_ERROR');
        }
    }

    public function store()
    {
        try {
            $data = $this->request->all();
            
            $errors = $this->validateMembershipData($data);
            
            if (!empty($errors)) {
                return $this->validationErrorResponse($errors);
            }

            $membership = $this->clubService->addMember(
                $data['club_id'],
                $data['student_id'],
                $data['role'] ?? 'member'
            );

            return $this->successResponse($membership, 'Membership created successfully', 201);
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 'MEMBERSHIP_CREATION_ERROR', null, 400);
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

            $memberships = $this->clubService->getClubMembers($filters['club_id'] ?? '');
            $membership = $memberships->first(fn($m) => $m->id === $id);

            if (!$membership) {
                return $this->notFoundResponse('Membership not found');
            }

            return $this->successResponse($membership, 'Membership retrieved successfully');
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 'MEMBERSHIP_RETRIEVAL_ERROR');
        }
    }

    public function update(string $id)
    {
        try {
            $data = $this->request->all();
            
            if (isset($data['role'])) {
                $errors = $this->validateMembershipData($data, true);
                
                if (!empty($errors)) {
                    return $this->validationErrorResponse($errors);
                }

                $membership = $this->clubService->updateMemberRole(
                    $data['club_id'] ?? '',
                    $id,
                    $data['role']
                );

                return $this->successResponse($membership, 'Membership role updated successfully');
            }

            return $this->errorResponse('Only role can be updated', 'INVALID_UPDATE', null, 400);
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 'MEMBERSHIP_UPDATE_ERROR', null, 400);
        }
    }

    public function destroy(string $id)
    {
        try {
            $data = $this->request->all();
            
            $clubId = $data['club_id'] ?? '';
            
            $result = $this->clubService->removeMember($clubId, $id);

            if (!$result) {
                return $this->notFoundResponse('Membership not found');
            }

            return $this->successResponse(null, 'Membership removed successfully');
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 'MEMBERSHIP_DELETION_ERROR', null, 400);
        }
    }

    public function student(string $studentId)
    {
        try {
            $memberships = $this->clubService->getStudentMemberships($studentId);

            return $this->successResponse($memberships, 'Student memberships retrieved successfully');
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 'STUDENT_MEMBERSHIPS_ERROR');
        }
    }

    private function validateMembershipData(array $data, bool $isUpdate = false): array
    {
        $errors = [];
        
        if (!$isUpdate && empty($data['club_id'])) {
            $errors['club_id'] = 'Club ID is required';
        }
        
        if (!$isUpdate && empty($data['student_id'])) {
            $errors['student_id'] = 'Student ID is required';
        }
        
        if (isset($data['role']) && !in_array($data['role'], ['member', 'officer', 'president', 'vice-president'])) {
            $errors['role'] = 'Invalid role. Must be member, officer, president, or vice-president';
        }

        return $errors;
    }
}
