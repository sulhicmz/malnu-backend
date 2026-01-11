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
use Psr\Http\Message\ResponseInterface;

/**
 * @Controller(prefix="api/alumni")
 * @Middleware(JWTMiddleware::class)
 */
class AlumniManagementController extends AbstractController
{
    private AlumniManagementService $alumniService;

    public function __construct(AlumniManagementService $alumniService)
    {
        $this->alumniService = $alumniService;
    }

    /**
     * Create a new alumni profile
     * @PostMapping(path="profiles")
     */
    public function createAlumni(): ResponseInterface
    {
        $data = $this->request->all();

        if (empty($data['student_id']) || empty($data['user_id'])) {
            return $this->response->json([
                'success' => false,
                'message' => 'Student ID and User ID are required'
            ])->withStatus(400);
        }

        try {
            $alumni = $this->alumniService->createAlumni($data);

            return $this->response->json([
                'success' => true,
                'data' => $alumni,
                'message' => 'Alumni profile created successfully'
            ]);
        } catch (\Exception $e) {
            return $this->response->json([
                'success' => false,
                'message' => 'Failed to create alumni profile: ' . $e->getMessage()
            ])->withStatus(500);
        }
    }

    /**
     * Get alumni by ID
     * @GetMapping(path="profiles/{id}")
     */
    public function getAlumni(string $id): ResponseInterface
    {
        try {
            $alumni = $this->alumniService->getAlumni($id);

            if (!$alumni) {
                return $this->response->json([
                    'success' => false,
                    'message' => 'Alumni not found'
                ])->withStatus(404);
            }

            return $this->response->json([
                'success' => true,
                'data' => $alumni
            ]);
        } catch (\Exception $e) {
            return $this->response->json([
                'success' => false,
                'message' => 'Failed to retrieve alumni: ' . $e->getMessage()
            ])->withStatus(500);
        }
    }

    /**
     * Update alumni profile
     * @PutMapping(path="profiles/{id}")
     */
    public function updateAlumni(string $id): ResponseInterface
    {
        $data = $this->request->all();

        try {
            $success = $this->alumniService->updateAlumni($id, $data);

            if (!$success) {
                return $this->response->json([
                    'success' => false,
                    'message' => 'Alumni not found'
                ])->withStatus(404);
            }

            return $this->response->json([
                'success' => true,
                'message' => 'Alumni profile updated successfully'
            ]);
        } catch (\Exception $e) {
            return $this->response->json([
                'success' => false,
                'message' => 'Failed to update alumni profile: ' . $e->getMessage()
            ])->withStatus(500);
        }
    }

    /**
     * Delete alumni profile
     * @DeleteMapping(path="profiles/{id}")
     */
    public function deleteAlumni(string $id): ResponseInterface
    {
        try {
            $success = $this->alumniService->deleteAlumni($id);

            if (!$success) {
                return $this->response->json([
                    'success' => false,
                    'message' => 'Alumni not found'
                ])->withStatus(404);
            }

            return $this->response->json([
                'success' => true,
                'message' => 'Alumni profile deleted successfully'
            ]);
        } catch (\Exception $e) {
            return $this->response->json([
                'success' => false,
                'message' => 'Failed to delete alumni profile: ' . $e->getMessage()
            ])->withStatus(500);
        }
    }

    /**
     * Get all alumni with filters
     * @GetMapping(path="profiles")
     */
    public function getAllAlumni(): ResponseInterface
    {
        $filters = $this->request->all();

        try {
            $alumni = $this->alumniService->getAllAlumni($filters);

            return $this->response->json([
                'success' => true,
                'data' => $alumni->items(),
                'pagination' => [
                    'current_page' => $alumni->currentPage(),
                    'per_page' => $alumni->perPage(),
                    'total' => $alumni->total(),
                    'last_page' => $alumni->lastPage(),
                ]
            ]);
        } catch (\Exception $e) {
            return $this->response->json([
                'success' => false,
                'message' => 'Failed to retrieve alumni: ' . $e->getMessage()
            ])->withStatus(500);
        }
    }

    /**
     * Create a career entry
     * @PostMapping(path="careers")
     */
    public function createCareer(): ResponseInterface
    {
        $data = $this->request->all();

        if (empty($data['alumni_id'])) {
            return $this->response->json([
                'success' => false,
                'message' => 'Alumni ID is required'
            ])->withStatus(400);
        }

        try {
            $career = $this->alumniService->createCareer($data);

            return $this->response->json([
                'success' => true,
                'data' => $career,
                'message' => 'Career entry created successfully'
            ]);
        } catch (\Exception $e) {
            return $this->response->json([
                'success' => false,
                'message' => 'Failed to create career entry: ' . $e->getMessage()
            ])->withStatus(500);
        }
    }

    /**
     * Update career entry
     * @PutMapping(path="careers/{id}")
     */
    public function updateCareer(string $id): ResponseInterface
    {
        $data = $this->request->all();

        try {
            $success = $this->alumniService->updateCareer($id, $data);

            if (!$success) {
                return $this->response->json([
                    'success' => false,
                    'message' => 'Career entry not found'
                ])->withStatus(404);
            }

            return $this->response->json([
                'success' => true,
                'message' => 'Career entry updated successfully'
            ]);
        } catch (\Exception $e) {
            return $this->response->json([
                'success' => false,
                'message' => 'Failed to update career entry: ' . $e->getMessage()
            ])->withStatus(500);
        }
    }

    /**
     * Delete career entry
     * @DeleteMapping(path="careers/{id}")
     */
    public function deleteCareer(string $id): ResponseInterface
    {
        try {
            $success = $this->alumniService->deleteCareer($id);

            if (!$success) {
                return $this->response->json([
                    'success' => false,
                    'message' => 'Career entry not found'
                ])->withStatus(404);
            }

            return $this->response->json([
                'success' => true,
                'message' => 'Career entry deleted successfully'
            ]);
        } catch (\Exception $e) {
            return $this->response->json([
                'success' => false,
                'message' => 'Failed to delete career entry: ' . $e->getMessage()
            ])->withStatus(500);
        }
    }

    /**
     * Create a donation
     * @PostMapping(path="donations")
     */
    public function createDonation(): ResponseInterface
    {
        $data = $this->request->all();

        if (empty($data['amount']) || empty($data['donation_type'])) {
            return $this->response->json([
                'success' => false,
                'message' => 'Amount and donation type are required'
            ])->withStatus(400);
        }

        try {
            $donation = $this->alumniService->createDonation($data);

            return $this->response->json([
                'success' => true,
                'data' => $donation,
                'message' => 'Donation recorded successfully'
            ]);
        } catch (\Exception $e) {
            return $this->response->json([
                'success' => false,
                'message' => 'Failed to record donation: ' . $e->getMessage()
            ])->withStatus(500);
        }
    }

    /**
     * Get all donations
     * @GetMapping(path="donations")
     */
    public function getDonations(): ResponseInterface
    {
        $filters = $this->request->all();

        try {
            $donations = $this->alumniService->getDonations($filters);

            return $this->response->json([
                'success' => true,
                'data' => $donations->items(),
                'pagination' => [
                    'current_page' => $donations->currentPage(),
                    'per_page' => $donations->perPage(),
                    'total' => $donations->total(),
                    'last_page' => $donations->lastPage(),
                ]
            ]);
        } catch (\Exception $e) {
            return $this->response->json([
                'success' => false,
                'message' => 'Failed to retrieve donations: ' . $e->getMessage()
            ])->withStatus(500);
        }
    }

    /**
     * Create an event
     * @PostMapping(path="events")
     */
    public function createEvent(): ResponseInterface
    {
        $data = $this->request->all();

        if (empty($data['name']) || empty($data['event_type']) || empty($data['event_date'])) {
            return $this->response->json([
                'success' => false,
                'message' => 'Name, event type, and event date are required'
            ])->withStatus(400);
        }

        try {
            $event = $this->alumniService->createEvent($data);

            return $this->response->json([
                'success' => true,
                'data' => $event,
                'message' => 'Event created successfully'
            ]);
        } catch (\Exception $e) {
            return $this->response->json([
                'success' => false,
                'message' => 'Failed to create event: ' . $e->getMessage()
            ])->withStatus(500);
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
            $success = $this->alumniService->updateEvent($id, $data);

            if (!$success) {
                return $this->response->json([
                    'success' => false,
                    'message' => 'Event not found'
                ])->withStatus(404);
            }

            return $this->response->json([
                'success' => true,
                'message' => 'Event updated successfully'
            ]);
        } catch (\Exception $e) {
            return $this->response->json([
                'success' => false,
                'message' => 'Failed to update event: ' . $e->getMessage()
            ])->withStatus(500);
        }
    }

    /**
     * Delete event
     * @DeleteMapping(path="events/{id}")
     */
    public function deleteEvent(string $id): ResponseInterface
    {
        try {
            $success = $this->alumniService->deleteEvent($id);

            if (!$success) {
                return $this->response->json([
                    'success' => false,
                    'message' => 'Event not found'
                ])->withStatus(404);
            }

            return $this->response->json([
                'success' => true,
                'message' => 'Event deleted successfully'
            ]);
        } catch (\Exception $e) {
            return $this->response->json([
                'success' => false,
                'message' => 'Failed to delete event: ' . $e->getMessage()
            ])->withStatus(500);
        }
    }

    /**
     * Get all events
     * @GetMapping(path="events")
     */
    public function getEvents(): ResponseInterface
    {
        $filters = $this->request->all();

        try {
            $events = $this->alumniService->getEvents($filters);

            return $this->response->json([
                'success' => true,
                'data' => $events->items(),
                'pagination' => [
                    'current_page' => $events->currentPage(),
                    'per_page' => $events->perPage(),
                    'total' => $events->total(),
                    'last_page' => $events->lastPage(),
                ]
            ]);
        } catch (\Exception $e) {
            return $this->response->json([
                'success' => false,
                'message' => 'Failed to retrieve events: ' . $e->getMessage()
            ])->withStatus(500);
        }
    }

    /**
     * Register for an event
     * @PostMapping(path="event-registrations")
     */
    public function registerForEvent(): ResponseInterface
    {
        $data = $this->request->all();

        if (empty($data['event_id']) || empty($data['name']) || empty($data['email'])) {
            return $this->response->json([
                'success' => false,
                'message' => 'Event ID, name, and email are required'
            ])->withStatus(400);
        }

        try {
            $registration = $this->alumniService->registerForEvent($data);

            return $this->response->json([
                'success' => true,
                'data' => $registration,
                'message' => 'Registration successful'
            ]);
        } catch (\Exception $e) {
            return $this->response->json([
                'success' => false,
                'message' => 'Failed to register: ' . $e->getMessage()
            ])->withStatus(400);
        }
    }

    /**
     * Cancel event registration
     * @DeleteMapping(path="event-registrations/{id}")
     */
    public function cancelRegistration(string $id): ResponseInterface
    {
        try {
            $success = $this->alumniService->cancelEventRegistration($id);

            if (!$success) {
                return $this->response->json([
                    'success' => false,
                    'message' => 'Registration not found'
                ])->withStatus(404);
            }

            return $this->response->json([
                'success' => true,
                'message' => 'Registration cancelled successfully'
            ]);
        } catch (\Exception $e) {
            return $this->response->json([
                'success' => false,
                'message' => 'Failed to cancel registration: ' . $e->getMessage()
            ])->withStatus(500);
        }
    }

    /**
     * Check in attendee
     * @PostMapping(path="event-registrations/{id}/check-in")
     */
    public function checkInAttendee(string $id): ResponseInterface
    {
        try {
            $success = $this->alumniService->checkInAttendee($id);

            if (!$success) {
                return $this->response->json([
                    'success' => false,
                    'message' => 'Registration not found'
                ])->withStatus(404);
            }

            return $this->response->json([
                'success' => true,
                'message' => 'Check-in successful'
            ]);
        } catch (\Exception $e) {
            return $this->response->json([
                'success' => false,
                'message' => 'Failed to check in: ' . $e->getMessage()
            ])->withStatus(500);
        }
    }

    /**
     * Create a mentorship
     * @PostMapping(path="mentorships")
     */
    public function createMentorship(): ResponseInterface
    {
        $data = $this->request->all();

        if (empty($data['mentor_id'])) {
            return $this->response->json([
                'success' => false,
                'message' => 'Mentor ID is required'
            ])->withStatus(400);
        }

        try {
            $mentorship = $this->alumniService->createMentorship($data);

            return $this->response->json([
                'success' => true,
                'data' => $mentorship,
                'message' => 'Mentorship created successfully'
            ]);
        } catch (\Exception $e) {
            return $this->response->json([
                'success' => false,
                'message' => 'Failed to create mentorship: ' . $e->getMessage()
            ])->withStatus(500);
        }
    }

    /**
     * Update mentorship
     * @PutMapping(path="mentorships/{id}")
     */
    public function updateMentorship(string $id): ResponseInterface
    {
        $data = $this->request->all();

        try {
            $success = $this->alumniService->updateMentorship($id, $data);

            if (!$success) {
                return $this->response->json([
                    'success' => false,
                    'message' => 'Mentorship not found'
                ])->withStatus(404);
            }

            return $this->response->json([
                'success' => true,
                'message' => 'Mentorship updated successfully'
            ]);
        } catch (\Exception $e) {
            return $this->response->json([
                'success' => false,
                'message' => 'Failed to update mentorship: ' . $e->getMessage()
            ])->withStatus(500);
        }
    }

    /**
     * Activate mentorship
     * @PostMapping(path="mentorships/{id}/activate")
     */
    public function activateMentorship(string $id): ResponseInterface
    {
        try {
            $success = $this->alumniService->activateMentorship($id);

            if (!$success) {
                return $this->response->json([
                    'success' => false,
                    'message' => 'Mentorship not found'
                ])->withStatus(404);
            }

            return $this->response->json([
                'success' => true,
                'message' => 'Mentorship activated successfully'
            ]);
        } catch (\Exception $e) {
            return $this->response->json([
                'success' => false,
                'message' => 'Failed to activate mentorship: ' . $e->getMessage()
            ])->withStatus(500);
        }
    }

    /**
     * Complete mentorship
     * @PostMapping(path="mentorships/{id}/complete")
     */
    public function completeMentorship(string $id): ResponseInterface
    {
        try {
            $success = $this->alumniService->completeMentorship($id);

            if (!$success) {
                return $this->response->json([
                    'success' => false,
                    'message' => 'Mentorship not found'
                ])->withStatus(404);
            }

            return $this->response->json([
                'success' => true,
                'message' => 'Mentorship completed successfully'
            ]);
        } catch (\Exception $e) {
            return $this->response->json([
                'success' => false,
                'message' => 'Failed to complete mentorship: ' . $e->getMessage()
            ])->withStatus(500);
        }
    }

    /**
     * Get all mentorships
     * @GetMapping(path="mentorships")
     */
    public function getMentorships(): ResponseInterface
    {
        $filters = $this->request->all();

        try {
            $mentorships = $this->alumniService->getMentorships($filters);

            return $this->response->json([
                'success' => true,
                'data' => $mentorships->items(),
                'pagination' => [
                    'current_page' => $mentorships->currentPage(),
                    'per_page' => $mentorships->perPage(),
                    'total' => $mentorships->total(),
                    'last_page' => $mentorships->lastPage(),
                ]
            ]);
        } catch (\Exception $e) {
            return $this->response->json([
                'success' => false,
                'message' => 'Failed to retrieve mentorships: ' . $e->getMessage()
            ])->withStatus(500);
        }
    }

    /**
     * Find available mentors
     * @GetMapping(path="available-mentors")
     */
    public function findAvailableMentors(): ResponseInterface
    {
        $criteria = $this->request->all();

        try {
            $mentors = $this->alumniService->findAvailableMentors($criteria);

            return $this->response->json([
                'success' => true,
                'data' => $mentors
            ]);
        } catch (\Exception $e) {
            return $this->response->json([
                'success' => false,
                'message' => 'Failed to find mentors: ' . $e->getMessage()
            ])->withStatus(500);
        }
    }

    /**
     * Create an engagement
     * @PostMapping(path="engagements")
     */
    public function createEngagement(): ResponseInterface
    {
        $data = $this->request->all();

        if (empty($data['alumni_id']) || empty($data['engagement_type'])) {
            return $this->response->json([
                'success' => false,
                'message' => 'Alumni ID and engagement type are required'
            ])->withStatus(400);
        }

        try {
            $engagement = $this->alumniService->createEngagement($data);

            return $this->response->json([
                'success' => true,
                'data' => $engagement,
                'message' => 'Engagement recorded successfully'
            ]);
        } catch (\Exception $e) {
            return $this->response->json([
                'success' => false,
                'message' => 'Failed to record engagement: ' . $e->getMessage()
            ])->withStatus(500);
        }
    }

    /**
     * Get all engagements
     * @GetMapping(path="engagements")
     */
    public function getEngagements(): ResponseInterface
    {
        $filters = $this->request->all();

        try {
            $engagements = $this->alumniService->getEngagements($filters);

            return $this->response->json([
                'success' => true,
                'data' => $engagements->items(),
                'pagination' => [
                    'current_page' => $engagements->currentPage(),
                    'per_page' => $engagements->perPage(),
                    'total' => $engagements->total(),
                    'last_page' => $engagements->lastPage(),
                ]
            ]);
        } catch (\Exception $e) {
            return $this->response->json([
                'success' => false,
                'message' => 'Failed to retrieve engagements: ' . $e->getMessage()
            ])->withStatus(500);
        }
    }

    /**
     * Get engagement report
     * @GetMapping(path="reports/engagement")
     */
    public function getEngagementReport(): ResponseInterface
    {
        $filters = $this->request->all();

        try {
            $report = $this->alumniService->getEngagementReport($filters);

            return $this->response->json([
                'success' => true,
                'data' => $report
            ]);
        } catch (\Exception $e) {
            return $this->response->json([
                'success' => false,
                'message' => 'Failed to generate engagement report: ' . $e->getMessage()
            ])->withStatus(500);
        }
    }

    /**
     * Get donation report
     * @GetMapping(path="reports/donation")
     */
    public function getDonationReport(): ResponseInterface
    {
        $filters = $this->request->all();

        try {
            $report = $this->alumniService->getDonationReport($filters);

            return $this->response->json([
                'success' => true,
                'data' => $report
            ]);
        } catch (\Exception $e) {
            return $this->response->json([
                'success' => false,
                'message' => 'Failed to generate donation report: ' . $e->getMessage()
            ])->withStatus(500);
        }
    }

    /**
     * Get alumni directory
     * @GetMapping(path="directory")
     */
    public function getAlumniDirectory(): ResponseInterface
    {
        $filters = $this->request->all();

        try {
            $directory = $this->alumniService->getAlumniDirectory($filters);

            return $this->response->json([
                'success' => true,
                'data' => $directory->items(),
                'pagination' => [
                    'current_page' => $directory->currentPage(),
                    'per_page' => $directory->perPage(),
                    'total' => $directory->total(),
                    'last_page' => $directory->lastPage(),
                ]
            ]);
        } catch (\Exception $e) {
            return $this->response->json([
                'success' => false,
                'message' => 'Failed to retrieve directory: ' . $e->getMessage()
            ])->withStatus(500);
        }
    }

    /**
     * Update privacy settings
     * @PutMapping(path="profiles/{id}/privacy")
     */
    public function updatePrivacySettings(string $id): ResponseInterface
    {
        $settings = $this->request->all();

        try {
            $success = $this->alumniService->updatePrivacySettings($id, $settings);

            if (!$success) {
                return $this->response->json([
                    'success' => false,
                    'message' => 'Alumni not found'
                ])->withStatus(404);
            }

            return $this->response->json([
                'success' => true,
                'message' => 'Privacy settings updated successfully'
            ]);
        } catch (\Exception $e) {
            return $this->response->json([
                'success' => false,
                'message' => 'Failed to update privacy settings: ' . $e->getMessage()
            ])->withStatus(500);
        }
    }

    /**
     * Verify alumni
     * @PostMapping(path="profiles/{id}/verify")
     */
    public function verifyAlumni(string $id): ResponseInterface
    {
        try {
            $success = $this->alumniService->verifyAlumni($id);

            if (!$success) {
                return $this->response->json([
                    'success' => false,
                    'message' => 'Alumni not found'
                ])->withStatus(404);
            }

            return $this->response->json([
                'success' => true,
                'message' => 'Alumni verified successfully'
            ]);
        } catch (\Exception $e) {
            return $this->response->json([
                'success' => false,
                'message' => 'Failed to verify alumni: ' . $e->getMessage()
            ])->withStatus(500);
        }
    }
}
