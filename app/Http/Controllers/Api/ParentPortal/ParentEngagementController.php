<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\ParentPortal;

use App\Http\Controllers\Api\BaseController;
use App\Services\ParentEngagementService;
use Hyperf\HttpServer\Annotation\Controller;
use Hyperf\HttpServer\Annotation\PostMapping;
use Hyperf\HttpServer\Annotation\GetMapping;
use Hyperf\HttpServer\Annotation\PutMapping;
use Hyperf\HttpServer\Annotation\DeleteMapping;
use Hyperf\HttpServer\Contract\RequestInterface;

#[Controller]
class ParentEngagementController extends BaseController
{
    public function __construct(
        private readonly ParentEngagementService $engagementService
    ) {}

    #[GetMapping('/api/parent/engagement/metrics')]
    public function getEngagementMetrics(RequestInterface $request)
    {
        try {
            $userId = $this->getAuthenticatedUserId();
            $startDate = $request->input('start_date');
            $endDate = $request->input('end_date');

            $metrics = $this->engagementService->getEngagementMetrics($userId, $startDate, $endDate);

            return $this->successResponse($metrics, 'Engagement metrics retrieved successfully');
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 'ENGAGEMENT_METRICS_ERROR', null, 500);
        }
    }

    #[PostMapping('/api/parent/events/registrations')]
    public function registerForEvent(RequestInterface $request)
    {
        try {
            $userId = $this->getAuthenticatedUserId();
            $eventId = $request->input('event_id');
            $studentId = $request->input('student_id');
            $numberOfAttendees = (int)$request->input('number_of_attendees', 1);
            $additionalInfo = $request->input('additional_info');

            $registration = $this->engagementService->registerForEvent($userId, $eventId, $studentId, $numberOfAttendees, $additionalInfo);

            return $this->successResponse($registration->toArray(), 'Event registration successful', 201);
        } catch (\RuntimeException $e) {
            return $this->errorResponse($e->getMessage(), 'REGISTRATION_ERROR', null, 400);
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 'EVENT_REGISTRATION_ERROR', null, 500);
        }
    }

    #[DeleteMapping('/api/parent/events/registrations/{eventId}')]
    public function cancelEventRegistration(RequestInterface $request)
    {
        try {
            $userId = $this->getAuthenticatedUserId();
            $eventId = $request->route('eventId');

            $success = $this->engagementService->cancelEventRegistration($userId, $eventId);

            if (!$success) {
                return $this->notFoundResponse('Event registration not found');
            }

            return $this->successResponse(null, 'Event registration cancelled successfully');
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 'CANCELLATION_ERROR', null, 500);
        }
    }

    #[GetMapping('/api/parent/events/registrations')]
    public function getParentRegistrations(RequestInterface $request)
    {
        try {
            $userId = $this->getAuthenticatedUserId();
            $status = $request->input('status');

            $registrations = $this->engagementService->getParentRegistrations($userId, $status);

            return $this->successResponse($registrations, 'Parent registrations retrieved successfully');
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 'REGISTRATIONS_RETRIEVAL_ERROR', null, 500);
        }
    }

    #[GetMapping('/api/parent/volunteer/opportunities')]
    public function getVolunteerOpportunities(RequestInterface $request)
    {
        try {
            $status = $request->input('status');

            $opportunities = $this->engagementService->getAvailableVolunteerOpportunities($status);

            return $this->successResponse($opportunities, 'Volunteer opportunities retrieved successfully');
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 'OPPORTUNITIES_RETRIEVAL_ERROR', null, 500);
        }
    }

    #[PostMapping('/api/parent/volunteer/signups')]
    public function signupForVolunteerOpportunity(RequestInterface $request)
    {
        try {
            $userId = $this->getAuthenticatedUserId();
            $opportunityId = $request->input('opportunity_id');
            $notes = $request->input('notes');

            $signup = $this->engagementService->signupForVolunteerOpportunity($userId, $opportunityId, $notes);

            return $this->successResponse($signup->toArray(), 'Volunteer signup successful', 201);
        } catch (\RuntimeException $e) {
            return $this->errorResponse($e->getMessage(), 'SIGNUP_ERROR', null, 400);
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 'VOLUNTEER_SIGNUP_ERROR', null, 500);
        }
    }

    #[DeleteMapping('/api/parent/volunteer/signups/{opportunityId}')]
    public function cancelVolunteerSignup(RequestInterface $request)
    {
        try {
            $userId = $this->getAuthenticatedUserId();
            $opportunityId = $request->route('opportunityId');

            $success = $this->engagementService->cancelVolunteerSignup($userId, $opportunityId);

            if (!$success) {
                return $this->notFoundResponse('Volunteer signup not found');
            }

            return $this->successResponse(null, 'Volunteer signup cancelled successfully');
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 'VOLUNTEER_CANCELLATION_ERROR', null, 500);
        }
    }

    #[GetMapping('/api/parent/volunteer/signups')]
    public function getParentVolunteerSignups(RequestInterface $request)
    {
        try {
            $userId = $this->getAuthenticatedUserId();

            $signups = $this->engagementService->getParentVolunteerSignups($userId);

            return $this->successResponse($signups, 'Parent volunteer signups retrieved successfully');
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 'SIGNUPS_RETRIEVAL_ERROR', null, 500);
        }
    }

    #[PutMapping('/api/parent/notifications/preferences')]
    public function updateNotificationPreferences(RequestInterface $request)
    {
        try {
            $userId = $this->getAuthenticatedUserId();
            $notificationType = $request->input('notification_type');
            $preferences = [
                'email_enabled' => $request->input('email_enabled', true),
                'sms_enabled' => $request->input('sms_enabled', false),
                'push_enabled' => $request->input('push_enabled', true),
                'in_app_enabled' => $request->input('in_app_enabled', true),
                'digest_mode' => $request->input('digest_mode', false),
                'digest_frequency' => $request->input('digest_frequency', 'daily'),
            ];

            $preference = $this->engagementService->updateNotificationPreferences($userId, $notificationType, $preferences);

            return $this->successResponse($preference->toArray(), 'Notification preferences updated successfully');
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 'PREFERENCES_UPDATE_ERROR', null, 500);
        }
    }

    #[GetMapping('/api/parent/notifications/preferences')]
    public function getNotificationPreferences(RequestInterface $request)
    {
        try {
            $userId = $this->getAuthenticatedUserId();

            $preferences = $this->engagementService->getNotificationPreferences($userId);

            return $this->successResponse($preferences, 'Notification preferences retrieved successfully');
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 'PREFERENCES_RETRIEVAL_ERROR', null, 500);
        }
    }

    private function getAuthenticatedUserId(): string
    {
        $user = $this->request->getAttribute('user');
        if (!$user) {
            throw new \RuntimeException('User not authenticated');
        }
        return $user['id'];
    }
}
