<?php

declare (strict_types = 1);

namespace App\Http\Controllers\Api\ClubManagement;

use App\Http\Controllers\Api\BaseController;
use App\Services\ClubManagementService;
use Psr\Http\Message\ServerRequestInterface;

class MembershipController extends BaseController
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
        $memberships = \App\Models\ClubManagement\ClubMembership::with(['club', 'student', 'student.user'])->get();
        return $this->successResponse($memberships);
    }

    public function store(ServerRequestInterface $request)
    {
        $data = $request->all();
        $validationErrors = $this->validateMembershipData($data);

        if (!empty($validationErrors)) {
            return $this->validationErrorResponse($validationErrors);
        }

        try {
            $membership = $this->clubManagementService->addMember($data['club_id'], $data['student_id'], $data['role'] ?? 'member');
            return $this->successResponse($membership, 'Member added successfully', 201);
        } catch (\Exception $e) {
            return $this->serverErrorResponse('Failed to add member: ' . $e->getMessage());
        }
    }

    public function show(string $id)
    {
        $membership = \App\Models\ClubManagement\ClubMembership::with(['club', 'student', 'student.user'])->find($id);

        if (!$membership) {
            return $this->notFoundResponse('Membership not found');
        }

        return $this->successResponse($membership);
    }

    public function update(ServerRequestInterface $request, string $id)
    {
        $membership = \App\Models\ClubManagement\ClubMembership::find($id);

        if (!$membership) {
            return $this->notFoundResponse('Membership not found');
        }

        $data = $request->all();
        $validationErrors = $this->validateMembershipData($data, true);

        if (!empty($validationErrors)) {
            return $this->validationErrorResponse($validationErrors);
        }

        try {
            $updatedMembership = $this->clubManagementService->updateMemberRole($membership->club_id, $membership->student_id, $data['role'] ?? $membership->role);
            return $this->successResponse($updatedMembership, 'Membership updated successfully');
        } catch (\Exception $e) {
            return $this->serverErrorResponse('Failed to update membership: ' . $e->getMessage());
        }
    }

    public function destroy(string $id)
    {
        $membership = \App\Models\ClubManagement\ClubMembership::find($id);

        if (!$membership) {
            return $this->notFoundResponse('Membership not found');
        }

        try {
            $deleted = $this->clubManagementService->removeMember($membership->club_id, $membership->student_id);
            if ($deleted) {
                return $this->successResponse(null, 'Member removed successfully');
            } else {
                return $this->serverErrorResponse('Failed to remove member');
            }
        } catch (\Exception $e) {
            return $this->serverErrorResponse('Failed to remove member: ' . $e->getMessage());
        }
    }

    private function validateMembershipData(array $data, bool $isUpdate = false): array
    {
        $errors = [];

        if (!$isUpdate && empty($data['club_id'] ?? '')) {
            $errors['club_id'] = 'Club ID is required';
        }

        if (!$isUpdate && empty($data['student_id'] ?? '')) {
            $errors['student_id'] = 'Student ID is required';
        }

        if (isset($data['role']) && !in_array($data['role'], ['member', 'officer', 'president', 'vice-president'])) {
            $errors['role'] = 'Invalid role. Must be one of: member, officer, president, vice-president';
        }

        if (isset($data['club_id']) && !\App\Models\ClubManagement\Club::find($data['club_id'])) {
            $errors['club_id'] = 'Club not found';
        }

        if (isset($data['student_id']) && !\App\Models\SchoolManagement\Student::find($data['student_id'])) {
            $errors['student_id'] = 'Student not found';
        }

        return $errors;
    }
}
