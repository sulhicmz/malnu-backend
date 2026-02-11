<?php

declare(strict_types=1);

namespace App\Http\Controllers\Alumni;

use App\Http\Controllers\AbstractController;
use App\Services\AlumniManagementService;
use Hyperf\HttpServer\Annotation\Controller;
use Hyperf\HttpServer\Annotation\GetMapping;
use Hyperf\HttpServer\Annotation\PostMapping;
use Hyperf\HttpServer\Annotation\PutMapping;
use Hyperf\HttpServer\Annotation\DeleteMapping;
use Hyperf\HttpServer\Annotation\Middleware;
use App\Http\Middleware\JWTMiddleware;
use Hyperf\HttpMessage\Stream\SwooleStream;
use Psr\Http\Message\ResponseInterface;

/**
 * @Controller(prefix="api/alumni")
 * @Middleware(JWTMiddleware::class)
 */
class AlumniController extends AbstractController
{
    private AlumniManagementService $alumniService;

    public function __construct(AlumniManagementService $alumniService)
    {
        $this->alumniService = $alumniService;
    }

    /**
     * Create alumni profile
     * @PostMapping(path="profiles")
     */
    public function createProfile(): ResponseInterface
    {
        $data = $this->request->all();

        if (empty($data['graduation_year'])) {
            return $this->errorResponse('Graduation year is required', null, null, 400);
        }

        try {
            $profile = $this->alumniService->createProfile($data);
            return $this->successResponse($profile, 'Alumni profile created successfully');
        } catch (\Exception $e) {
            return $this->serverErrorResponse('Failed to create alumni profile: ' . $e->getMessage());
        }
    }

    /**
     * Get alumni profile by ID
     * @GetMapping(path="profiles/{id}")
     */
    public function getProfile(string $id): ResponseInterface
    {
        try {
            $profile = $this->alumniService->getProfile($id);

            if (!$profile) {
                return $this->notFoundResponse('Alumni profile not found');
            }

            return $this->successResponse($profile);
        } catch (\Exception $e) {
            return $this->serverErrorResponse('Failed to retrieve alumni profile: ' . $e->getMessage());
        }
    }

    /**
     * Update alumni profile
     * @PutMapping(path="profiles/{id}")
     */
    public function updateProfile(string $id): ResponseInterface
    {
        $data = $this->request->all();

        try {
            $updated = $this->alumniService->updateProfile($id, $data);

            if (!$updated) {
                return $this->notFoundResponse('Alumni profile not found');
            }

            return $this->successResponse(null, 'Alumni profile updated successfully');
        } catch (\Exception $e) {
            return $this->serverErrorResponse('Failed to update alumni profile: ' . $e->getMessage());
        }
    }

    /**
     * Delete alumni profile
     * @DeleteMapping(path="profiles/{id}")
     */
    public function deleteProfile(string $id): ResponseInterface
    {
        try {
            $deleted = $this->alumniService->deleteProfile($id);

            if (!$deleted) {
                return $this->notFoundResponse('Alumni profile not found');
            }

            return $this->successResponse(null, 'Alumni profile deleted successfully');
        } catch (\Exception $e) {
            return $this->serverErrorResponse('Failed to delete alumni profile: ' . $e->getMessage());
        }
    }

    /**
     * Get alumni directory with filters
     * @GetMapping(path="directory")
     */
    public function getDirectory(): ResponseInterface
    {
        $filters = $this->request->all();

        try {
            $directory = $this->alumniService->getAlumniDirectory($filters);
            return $this->successResponse($directory);
        } catch (\Exception $e) {
            return $this->serverErrorResponse('Failed to retrieve alumni directory: ' . $e->getMessage());
        }
    }

    /**
     * Add career to alumni profile
     * @PostMapping(path="profiles/{id}/careers")
     */
    public function addCareer(string $id): ResponseInterface
    {
        $data = $this->request->all();

        try {
            $career = $this->alumniService->addCareer($id, $data);
            return $this->successResponse($career, 'Career added successfully');
        } catch (\Exception $e) {
            return $this->serverErrorResponse('Failed to add career: ' . $e->getMessage());
        }
    }

    /**
     * Update career
     * @PutMapping(path="careers/{id}")
     */
    public function updateCareer(string $id): ResponseInterface
    {
        $data = $this->request->all();

        try {
            $updated = $this->alumniService->updateCareer($id, $data);

            if (!$updated) {
                return $this->notFoundResponse('Career not found');
            }

            return $this->successResponse(null, 'Career updated successfully');
        } catch (\Exception $e) {
            return $this->serverErrorResponse('Failed to update career: ' . $e->getMessage());
        }
    }

    /**
     * Delete career
     * @DeleteMapping(path="careers/{id}")
     */
    public function deleteCareer(string $id): ResponseInterface
    {
        try {
            $deleted = $this->alumniService->deleteCareer($id);

            if (!$deleted) {
                return $this->notFoundResponse('Career not found');
            }

            return $this->successResponse(null, 'Career deleted successfully');
        } catch (\Exception $e) {
            return $this->serverErrorResponse('Failed to delete career: ' . $e->getMessage());
        }
    }

    /**
     * Add achievement
     * @PostMapping(path="profiles/{id}/achievements")
     */
    public function addAchievement(string $id): ResponseInterface
    {
        $data = $this->request->all();

        try {
            $achievement = $this->alumniService->addAchievement($id, $data);
            return $this->successResponse($achievement, 'Achievement added successfully');
        } catch (\Exception $e) {
            return $this->serverErrorResponse('Failed to add achievement: ' . $e->getMessage());
        }
    }

    /**
     * Update achievement
     * @PutMapping(path="achievements/{id}")
     */
    public function updateAchievement(string $id): ResponseInterface
    {
        $data = $this->request->all();

        try {
            $updated = $this->alumniService->updateAchievement($id, $data);

            if (!$updated) {
                return $this->notFoundResponse('Achievement not found');
            }

            return $this->successResponse(null, 'Achievement updated successfully');
        } catch (\Exception $e) {
            return $this->serverErrorResponse('Failed to update achievement: ' . $e->getMessage());
        }
    }

    /**
     * Delete achievement
     * @DeleteMapping(path="achievements/{id}")
     */
    public function deleteAchievement(string $id): ResponseInterface
    {
        try {
            $deleted = $this->alumniService->deleteAchievement($id);

            if (!$deleted) {
                return $this->notFoundResponse('Achievement not found');
            }

            return $this->successResponse(null, 'Achievement deleted successfully');
        } catch (\Exception $e) {
            return $this->serverErrorResponse('Failed to delete achievement: ' . $e->getMessage());
        }
    }

    /**
     * Create mentorship match
     * @PostMapping(path="mentorships")
     */
    public function createMentorship(): ResponseInterface
    {
        $data = $this->request->all();

        if (empty($data['alumni_id']) || empty($data['student_id'])) {
            return $this->errorResponse('Alumni ID and Student ID are required', null, null, 400);
        }

        try {
            $mentorship = $this->alumniService->createMentorship($data);
            return $this->successResponse($mentorship, 'Mentorship created successfully');
        } catch (\Exception $e) {
            return $this->serverErrorResponse('Failed to create mentorship: ' . $e->getMessage());
        }
    }

    /**
     * Update mentorship status
     * @PutMapping(path="mentorships/{id}")
     */
    public function updateMentorship(string $id): ResponseInterface
    {
        $data = $this->request->all();

        try {
            $updated = $this->alumniService->updateMentorship($id, $data);

            if (!$updated) {
                return $this->notFoundResponse('Mentorship not found');
            }

            return $this->successResponse(null, 'Mentorship updated successfully');
        } catch (\Exception $e) {
            return $this->serverErrorResponse('Failed to update mentorship: ' . $e->getMessage());
        }
    }

    /**
     * Get alumni's mentorships
     * @GetMapping(path="profiles/{id}/mentorships")
     */
    public function getAlumniMentorships(string $id): ResponseInterface
    {
        try {
            $mentorships = $this->alumniService->getMentorships($id);
            return $this->successResponse($mentorships);
        } catch (\Exception $e) {
            return $this->serverErrorResponse('Failed to retrieve mentorships: ' . $e->getMessage());
        }
    }

    /**
     * Get student's mentorships
     * @GetMapping(path="mentorships/student/{id}")
     */
    public function getStudentMentorships(string $id): ResponseInterface
    {
        try {
            $mentorships = $this->alumniService->getStudentMentorships($id);
            return $this->successResponse($mentorships);
        } catch (\Exception $e) {
            return $this->serverErrorResponse('Failed to retrieve mentorships: ' . $e->getMessage());
        }
    }

    /**
     * Record donation
     * @PostMapping(path="profiles/{id}/donations")
     */
    public function recordDonation(string $id): ResponseInterface
    {
        $data = $this->request->all();

        if (empty($data['amount']) || empty($data['donation_type'])) {
            return $this->errorResponse('Amount and donation type are required', null, null, 400);
        }

        try {
            $donation = $this->alumniService->recordDonation($id, $data);
            return $this->successResponse($donation, 'Donation recorded successfully');
        } catch (\Exception $e) {
            return $this->serverErrorResponse('Failed to record donation: ' . $e->getMessage());
        }
    }

    /**
     * Get alumni donations
     * @GetMapping(path="profiles/{id}/donations")
     */
    public function getDonations(string $id): ResponseInterface
    {
        try {
            $donations = $this->alumniService->getDonations($id);
            return $this->successResponse($donations);
        } catch (\Exception $e) {
            return $this->serverErrorResponse('Failed to retrieve donations: ' . $e->getMessage());
        }
    }

    /**
     * Create alumni event
     * @PostMapping(path="events")
     */
    public function createEvent(): ResponseInterface
    {
        $data = $this->request->all();

        if (empty($data['title']) || empty($data['event_date'])) {
            return $this->errorResponse('Title and event date are required', null, null, 400);
        }

        try {
            $event = $this->alumniService->createEvent($data);
            return $this->successResponse($event, 'Event created successfully');
        } catch (\Exception $e) {
            return $this->serverErrorResponse('Failed to create event: ' . $e->getMessage());
        }
    }

    /**
     * Update event
     * @PutMapping(path="events/{id}")
     */
    public function updateEvent(string $id): ResponseInterface
    {
        $data = $this->request->all();

        try {
            $updated = $this->alumniService->updateEvent($id, $data);

            if (!$updated) {
                return $this->notFoundResponse('Event not found');
            }

            return $this->successResponse(null, 'Event updated successfully');
        } catch (\Exception $e) {
            return $this->serverErrorResponse('Failed to update event: ' . $e->getMessage());
        }
    }

    /**
     * Delete event
     * @DeleteMapping(path="events/{id}")
     */
    public function deleteEvent(string $id): ResponseInterface
    {
        try {
            $deleted = $this->alumniService->deleteEvent($id);

            if (!$deleted) {
                return $this->notFoundResponse('Event not found');
            }

            return $this->successResponse(null, 'Event deleted successfully');
        } catch (\Exception $e) {
            return $this->serverErrorResponse('Failed to delete event: ' . $e->getMessage());
        }
    }

    /**
     * Get upcoming events
     * @GetMapping(path="events/upcoming")
     */
    public function getUpcomingEvents(): ResponseInterface
    {
        try {
            $events = $this->alumniService->getUpcomingEvents();
            return $this->successResponse($events);
        } catch (\Exception $e) {
            return $this->serverErrorResponse('Failed to retrieve upcoming events: ' . $e->getMessage());
        }
    }

    /**
     * Register for event
     * @PostMapping(path="events/{id}/register")
     */
    public function registerForEvent(string $id): ResponseInterface
    {
        $data = $this->request->all();

        if (empty($data['alumni_id'])) {
            return $this->errorResponse('Alumni ID is required', null, null, 400);
        }

        try {
            $registration = $this->alumniService->registerForEvent($id, $data['alumni_id'], $data);
            return $this->successResponse($registration, 'Registration successful');
        } catch (\Exception $e) {
            return $this->serverErrorResponse('Failed to register for event: ' . $e->getMessage());
        }
    }

    /**
     * Update registration
     * @PutMapping(path="registrations/{id}")
     */
    public function updateRegistration(string $id): ResponseInterface
    {
        $data = $this->request->all();

        try {
            $updated = $this->alumniService->updateRegistration($id, $data);

            if (!$updated) {
                return $this->notFoundResponse('Registration not found');
            }

            return $this->successResponse(null, 'Registration updated successfully');
        } catch (\Exception $e) {
            return $this->serverErrorResponse('Failed to update registration: ' . $e->getMessage());
        }
    }

    /**
     * Cancel registration
     * @PostMapping(path="registrations/{id}/cancel")
     */
    public function cancelRegistration(string $id): ResponseInterface
    {
        try {
            $updated = $this->alumniService->cancelRegistration($id);

            if (!$updated) {
                return $this->notFoundResponse('Registration not found');
            }

            return $this->successResponse(null, 'Registration cancelled successfully');
        } catch (\Exception $e) {
            return $this->serverErrorResponse('Failed to cancel registration: ' . $e->getMessage());
        }
    }

    /**
     * Get alumni statistics
     * @GetMapping(path="statistics")
     */
    public function getStatistics(): ResponseInterface
    {
        try {
            $statistics = $this->alumniService->getAlumniStatistics();
            return $this->successResponse($statistics);
        } catch (\Exception $e) {
            return $this->serverErrorResponse('Failed to retrieve statistics: ' . $e->getMessage());
        }
    }
}
