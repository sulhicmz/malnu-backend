<?php

declare(strict_types=1);

namespace App\Http\Controllers\Calendar;

use App\Http\Controllers\AbstractController;
use App\Services\CalendarService;
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
 * @Controller(prefix="api/calendar")
 * @Middleware(JWTMiddleware::class)
 */
class CalendarController extends AbstractController
{
    private CalendarService $calendarService;

    public function __construct(CalendarService $calendarService)
    {
        $this->calendarService = $calendarService;
    }

    /**
     * Create a new calendar
     * @PostMapping(path="calendars")
     */
    public function createCalendar(): ResponseInterface
    {
        $data = $this->request->all();

        // Validate required fields
        if (empty($data['name'])) {
            return $this->errorResponse('Calendar name is required', 'VALIDATION_ERROR', ['name' => ['Calendar name is required']], 400);
        }

        try {
            $calendar = $this->calendarService->createCalendar($data);

            return $this->successResponse($calendar, 'Calendar created successfully');
        } catch (\Exception $e) {
            return $this->serverErrorResponse('Failed to create calendar: ' . $e->getMessage());
        }
    }

    /**
     * Get calendar by ID
     * @GetMapping(path="calendars/{id}")
     */
    public function getCalendar(string $id): ResponseInterface
    {
        try {
            $calendar = $this->calendarService->getCalendar($id);

            if (!$calendar) {
                return $this->notFoundResponse('Calendar not found');
            }

            return $this->successResponse($calendar);
        } catch (\Exception $e) {
            return $this->serverErrorResponse('Failed to retrieve calendar: ' . $e->getMessage());
        }
    }

    /**
     * Update calendar
     * @PutMapping(path="calendars/{id}")
     */
    public function updateCalendar(string $id): ResponseInterface
    {
        $data = $this->request->all();

        try {
            $result = $this->calendarService->updateCalendar($id, $data);

            if (!$result) {
                return $this->notFoundResponse('Calendar not found');
            }

            $calendar = $this->calendarService->getCalendar($id);

            return $this->successResponse($calendar, 'Calendar updated successfully');
        } catch (\Exception $e) {
            return $this->serverErrorResponse('Failed to update calendar: ' . $e->getMessage());
        }
    }

    /**
     * Delete calendar
     * @DeleteMapping(path="calendars/{id}")
     */
    public function deleteCalendar(string $id): ResponseInterface
    {
        try {
            $result = $this->calendarService->deleteCalendar($id);

            if (!$result) {
                return $this->notFoundResponse('Calendar not found');
            }

            return $this->successResponse(null, 'Calendar deleted successfully');
        } catch (\Exception $e) {
            return $this->serverErrorResponse('Failed to delete calendar: ' . $e->getMessage());
        }
    }

    /**
     * Create a new event
     * @PostMapping(path="events")
     */
    public function createEvent(): ResponseInterface
    {
        $data = $this->request->all();

        // Validate required fields
        if (empty($data['calendar_id']) || empty($data['title']) || empty($data['start_date']) || empty($data['end_date'])) {
            return $this->errorResponse('Calendar ID, title, start date, and end date are required', 'VALIDATION_ERROR', [
                'calendar_id' => ['Calendar ID is required'],
                'title' => ['Title is required'],
                'start_date' => ['Start date is required'],
                'end_date' => ['End date is required']
            ], 400);
        }

        try {
            $event = $this->calendarService->createEvent($data);

            return $this->successResponse($event, 'Event created successfully');
        } catch (\Exception $e) {
            return $this->serverErrorResponse('Failed to create event: ' . $e->getMessage());
        }
    }

    /**
     * Get event by ID
     * @GetMapping(path="events/{id}")
     */
    public function getEvent(string $id): ResponseInterface
    {
        try {
            $event = $this->calendarService->getEvent($id);

            if (!$event) {
                return $this->notFoundResponse('Event not found');
            }

            return $this->successResponse($event);
        } catch (\Exception $e) {
            return $this->serverErrorResponse('Failed to retrieve event: ' . $e->getMessage());
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
            $result = $this->calendarService->updateEvent($id, $data);

            if (!$result) {
                return $this->notFoundResponse('Event not found');
            }

            $event = $this->calendarService->getEvent($id);

            return $this->successResponse($event, 'Event updated successfully');
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
            $result = $this->calendarService->deleteEvent($id);

            if (!$result) {
                return $this->notFoundResponse('Event not found');
            }

            return $this->successResponse(null, 'Event deleted successfully');
        } catch (\Exception $e) {
            return $this->serverErrorResponse('Failed to delete event: ' . $e->getMessage());
        }
    }

    /**
     * Get events by date range
     * @GetMapping(path="calendars/{calendarId}/events")
     */
    public function getEventsByDateRange(string $calendarId): ResponseInterface
    {
        $startDate = $this->request->query('start_date');
        $endDate = $this->request->query('end_date');
        $category = $this->request->query('category');
        $priority = $this->request->query('priority');

        if (empty($startDate) || empty($endDate)) {
            return $this->errorResponse('Start date and end date are required', 'VALIDATION_ERROR', [
                'start_date' => ['Start date is required'],
                'end_date' => ['End date is required']
            ], 400);
        }

        try {
            $carbon = new \DateTime($startDate);
            $endDateObj = new \DateTime($endDate);

            $filters = [];
            if ($category) $filters['category'] = $category;
            if ($priority) $filters['priority'] = $priority;

            $events = $this->calendarService->getEventsByDateRange($calendarId, $carbon, $endDateObj, $filters);

            return $this->successResponse($events);
        } catch (\Exception $e) {
            return $this->serverErrorResponse('Failed to retrieve events: ' . $e->getMessage());
        }
    }

    /**
     * Register for an event
     * @PostMapping(path="events/{eventId}/register")
     */
    public function registerForEvent(string $eventId): ResponseInterface
    {
        $userId = $this->request->getAttribute('user_id');
        $data = $this->request->all();

        try {
            $result = $this->calendarService->registerForEvent($eventId, $userId, $data);

            return $this->successResponse(null, 'Successfully registered for event');
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 'REGISTRATION_ERROR', null, 400);
        }
    }

    /**
     * Share calendar with user
     * @PostMapping(path="calendars/{calendarId}/share")
     */
    public function shareCalendar(string $calendarId): ResponseInterface
    {
        $data = $this->request->all();

        if (empty($data['user_id']) || empty($data['permission_type'])) {
            return $this->errorResponse('User ID and permission type are required', 'VALIDATION_ERROR', [
                'user_id' => ['User ID is required'],
                'permission_type' => ['Permission type is required']
            ], 400);
        }

        try {
            $expiresAt = null;
            if (!empty($data['expires_at'])) {
                $expiresAt = new \DateTime($data['expires_at']);
            }

            $result = $this->calendarService->shareCalendar($calendarId, $data['user_id'], $data['permission_type'], $expiresAt);

            return $this->successResponse(null, 'Calendar shared successfully');
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 'SHARE_ERROR', null, 400);
        }
    }

    /**
     * Book a resource
     * @PostMapping(path="resources/book")
     */
    public function bookResource(): ResponseInterface
    {
        $data = $this->request->all();

        if (empty($data['resource_type']) || empty($data['resource_id']) || empty($data['start_time']) || empty($data['end_time'])) {
            return $this->errorResponse('Resource type, resource ID, start time, and end time are required', 'VALIDATION_ERROR', [
                'resource_type' => ['Resource type is required'],
                'resource_id' => ['Resource ID is required'],
                'start_time' => ['Start time is required'],
                'end_time' => ['End time is required']
            ], 400);
        }

        try {
            $booking = $this->calendarService->bookResource($data);

            return $this->successResponse($booking, 'Resource booked successfully');
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 'BOOKING_ERROR', null, 400);
        }
    }
}