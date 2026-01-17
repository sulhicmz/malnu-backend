<?php

declare(strict_types=1);

namespace App\Http\Controllers\Extracurricular;

use App\Http\Controllers\Api\BaseController;
use App\Services\ClubManagementService;
use Hyperf\HttpServer\Contract\RequestInterface;
use Hyperf\HttpServer\Contract\ResponseInterface;
use Psr\Container\ContainerInterface;

class ClubController extends BaseController
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
            
            if ($this->request->input('category')) {
                $filters['category'] = $this->request->input('category');
            }
            
            if ($this->request->input('advisor_id')) {
                $filters['advisor_id'] = $this->request->input('advisor_id');
            }

            $clubs = $this->clubService->getClubs($filters);

            return $this->successResponse($clubs, 'Clubs retrieved successfully');
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 'CLUB_RETRIEVAL_ERROR');
        }
    }

    public function store()
    {
        try {
            $data = $this->request->all();
            
            $errors = $this->validateClubData($data);
            
            if (!empty($errors)) {
                return $this->validationErrorResponse($errors);
            }

            $club = $this->clubService->createClub($data);

            return $this->successResponse($club, 'Club created successfully', 201);
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 'CLUB_CREATION_ERROR', null, 400);
        }
    }

    public function show(string $id)
    {
        try {
            $club = $this->clubService->getClub($id);
            
            return $this->successResponse($club, 'Club retrieved successfully');
        } catch (\Exception $e) {
            return $this->notFoundResponse('Club not found');
        }
    }

    public function update(string $id)
    {
        try {
            $data = $this->request->all();
            
            $errors = $this->validateClubData($data, true);
            
            if (!empty($errors)) {
                return $this->validationErrorResponse($errors);
            }

            $club = $this->clubService->updateClub($id, $data);

            return $this->successResponse($club, 'Club updated successfully');
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 'CLUB_UPDATE_ERROR', null, 400);
        }
    }

    public function destroy(string $id)
    {
        try {
            $this->clubService->deleteClub($id);

            return $this->successResponse(null, 'Club deleted successfully');
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 'CLUB_DELETION_ERROR', null, 400);
        }
    }

    public function statistics(string $id)
    {
        try {
            $stats = $this->clubService->getClubStatistics($id);

            return $this->successResponse($stats, 'Club statistics retrieved successfully');
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 'STATISTICS_ERROR');
        }
    }

    public function upcoming()
    {
        try {
            $filters = [];
            
            if ($this->request->input('category')) {
                $filters['category'] = $this->request->input('category');
            }

            $clubs = $this->clubService->getClubs($filters);

            $upcomingActivities = [];
            foreach ($clubs as $club) {
                $activities = $this->clubService->getClubActivities($club->id, 'scheduled');
                $upcomingActivities = array_merge($upcomingActivities, $activities->toArray());
            }

            return $this->successResponse($upcomingActivities, 'Upcoming activities retrieved successfully');
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 'UPCOMING_ERROR');
        }
    }

    private function validateClubData(array $data, bool $isUpdate = false): array
    {
        $errors = [];
        
        if (!$isUpdate && empty($data['name'])) {
            $errors['name'] = 'Club name is required';
        }
        
        if (isset($data['max_members']) && (!is_numeric($data['max_members']) || (int)$data['max_members'] < 1)) {
            $errors['max_members'] = 'Max members must be a positive number';
        }
        
        if (isset($data['category']) && !in_array($data['category'], ['academic', 'sports', 'arts', 'music', 'technology', 'social', 'service', 'other'])) {
            $errors['category'] = 'Invalid category';
        }

        return $errors;
    }
}
