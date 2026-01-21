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

        if (empty($data['name'])) {
            return $this->errorResponse('Calendar name is required', null, null, 400);
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

        if (empty($data['calendar_id']) || empty($data['title']) || empty($data['start_date']) || empty($data['end_date'])) {
            return $this->errorResponse('Calendar ID, title, start date, and end date are required', null, null, 400);
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
            return $this->errorResponse('Start date and end date are required', null, null, 400);
        }

        try {
            $startDateObj = new \DateTime($startDate);
            $endDateObj = new \DateTime($endDate);

            $filters = [];
            if ($category) $filters['category'] = $category;
            if ($priority) $filters['priority'] = $priority;

            $events = $this->calendarService->getEventsByDateRange($calendarId, $startDateObj, $endDateObj, $filters);
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
            return $this->errorResponse($e->getMessage(), null, null, 400);
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
            return $this->errorResponse('User ID and permission type are required', null, null, 400);
        }

        try {
            $expiresAt = null;
            if (!empty($data['expires_at'])) {
                $expiresAt = new \DateTime($data['expires_at']);
            }

            $result = $this->calendarService->shareCalendar($calendarId, $data['user_id'], $data['permission_type'], $expiresAt);
            return $this->successResponse(null, 'Calendar shared successfully');
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), null, null, 400);
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
            return $this->errorResponse('Resource type, resource ID, start time, and end time are required', null, null, 400);
        }

        try {
            $booking = $this->calendarService->bookResource($data);
            return $this->successResponse($booking, 'Resource booked successfully');
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), null, null, 400);
        }
    }

    /**
     * Create a new academic term
     * @PostMapping(path="academic-terms")
     */
    public function createAcademicTerm(): ResponseInterface
    {
        $data = $this->request->all();

        if (empty($data['name']) || empty($data['academic_year']) || empty($data['start_date']) || empty($data['end_date'])) {
            return $this->errorResponse('Name, academic year, start date, and end date are required', null, null, 400);
        }

        try {
            $term = $this->calendarService->createAcademicTerm($data);
            return $this->successResponse($term, 'Academic term created successfully');
        } catch (\Exception $e) {
            return $this->serverErrorResponse('Failed to create academic term: ' . $e->getMessage());
        }
    }

    /**
     * Get academic term by ID
     * @GetMapping(path="academic-terms/{id}")
     */
    public function getAcademicTerm(string $id): ResponseInterface
    {
        try {
            $term = $this->calendarService->getAcademicTerm($id);

            if (!$term) {
                return $this->notFoundResponse('Academic term not found');
            }

            return $this->successResponse($term);
        } catch (\Exception $e) {
            return $this->serverErrorResponse('Failed to retrieve academic term: ' . $e->getMessage());
        }
    }

    /**
     * Get all academic terms
     * @GetMapping(path="academic-terms")
     */
    public function getAllAcademicTerms(): ResponseInterface
    {
        try {
            $terms = $this->calendarService->getAllAcademicTerms();
            return $this->successResponse($terms);
        } catch (\Exception $e) {
            return $this->serverErrorResponse('Failed to retrieve academic terms: ' . $e->getMessage());
        }
    }

    /**
     * Get current academic term
     * @GetMapping(path="academic-terms/current")
     */
    public function getCurrentAcademicTerm(): ResponseInterface
    {
        try {
            $term = $this->calendarService->getCurrentAcademicTerm();

            if (!$term) {
                return $this->notFoundResponse('No current academic term found');
            }

            return $this->successResponse($term);
        } catch (\Exception $e) {
            return $this->serverErrorResponse('Failed to retrieve current academic term: ' . $e->getMessage());
        }
    }

    /**
     * Update academic term
     * @PutMapping(path="academic-terms/{id}")
     */
    public function updateAcademicTerm(string $id): ResponseInterface
    {
        $data = $this->request->all();

        try {
            $result = $this->calendarService->updateAcademicTerm($id, $data);

            if (!$result) {
                return $this->notFoundResponse('Academic term not found');
            }

            $term = $this->calendarService->getAcademicTerm($id);
            return $this->successResponse($term, 'Academic term updated successfully');
        } catch (\Exception $e) {
            return $this->serverErrorResponse('Failed to update academic term: ' . $e->getMessage());
        }
    }

    /**
     * Delete academic term
     * @DeleteMapping(path="academic-terms/{id}")
     */
    public function deleteAcademicTerm(string $id): ResponseInterface
    {
        try {
            $result = $this->calendarService->deleteAcademicTerm($id);

            if (!$result) {
                return $this->notFoundResponse('Academic term not found');
            }

            return $this->successResponse(null, 'Academic term deleted successfully');
        } catch (\Exception $e) {
            return $this->serverErrorResponse('Failed to delete academic term: ' . $e->getMessage());
        }
    }

    /**
     * Create a new holiday
     * @PostMapping(path="holidays")
     */
    public function createHoliday(): ResponseInterface
    {
        $data = $this->request->all();

        if (empty($data['name']) || empty($data['start_date']) || empty($data['end_date'])) {
            return $this->errorResponse('Name, start date, and end date are required', null, null, 400);
        }

        try {
            $holiday = $this->calendarService->createHoliday($data);
            return $this->successResponse($holiday, 'Holiday created successfully');
        } catch (\Exception $e) {
            return $this->serverErrorResponse('Failed to create holiday: ' . $e->getMessage());
        }
    }

    /**
     * Get holiday by ID
     * @GetMapping(path="holidays/{id}")
     */
    public function getHoliday(string $id): ResponseInterface
    {
        try {
            $holiday = $this->calendarService->getHoliday($id);

            if (!$holiday) {
                return $this->notFoundResponse('Holiday not found');
            }

            return $this->successResponse($holiday);
        } catch (\Exception $e) {
            return $this->serverErrorResponse('Failed to retrieve holiday: ' . $e->getMessage());
        }
    }

    /**
     * Get holidays by date range
     * @GetMapping(path="holidays")
     */
    public function getHolidays(): ResponseInterface
    {
        $startDate = $this->request->query('start_date');
        $endDate = $this->request->query('end_date');
        $type = $this->request->query('type');
        $schoolWide = $this->request->query('school_wide');

        if (empty($startDate) || empty($endDate)) {
            return $this->errorResponse('Start date and end date are required', null, null, 400);
        }

        try {
            $startDateObj = new \DateTime($startDate);
            $endDateObj = new \DateTime($endDate);

            $filters = [];
            if ($type) $filters['type'] = $type;
            if ($schoolWide !== null) $filters['school_wide'] = filter_var($schoolWide, FILTER_VALIDATE_BOOLEAN);

            $holidays = $this->calendarService->getHolidaysByDateRange($startDateObj, $endDateObj, $filters);
            return $this->successResponse($holidays);
        } catch (\Exception $e) {
            return $this->serverErrorResponse('Failed to retrieve holidays: ' . $e->getMessage());
        }
    }

    /**
     * Get upcoming holidays
     * @GetMapping(path="holidays/upcoming")
     */
    public function getUpcomingHolidays(): ResponseInterface
    {
        $days = $this->request->query('days', 90);

        try {
            $holidays = $this->calendarService->getUpcomingHolidays((int) $days);
            return $this->successResponse($holidays);
        } catch (\Exception $e) {
            return $this->serverErrorResponse('Failed to retrieve upcoming holidays: ' . $e->getMessage());
        }
    }

    /**
     * Update holiday
     * @PutMapping(path="holidays/{id}")
     */
    public function updateHoliday(string $id): ResponseInterface
    {
        $data = $this->request->all();

        try {
            $result = $this->calendarService->updateHoliday($id, $data);

            if (!$result) {
                return $this->notFoundResponse('Holiday not found');
            }

            $holiday = $this->calendarService->getHoliday($id);
            return $this->successResponse($holiday, 'Holiday updated successfully');
        } catch (\Exception $e) {
            return $this->serverErrorResponse('Failed to update holiday: ' . $e->getMessage());
        }
    }

    /**
     * Delete holiday
     * @DeleteMapping(path="holidays/{id}")
     */
    public function deleteHoliday(string $id): ResponseInterface
    {
        try {
            $result = $this->calendarService->deleteHoliday($id);

            if (!$result) {
                return $this->notFoundResponse('Holiday not found');
            }

            return $this->successResponse(null, 'Holiday deleted successfully');
        } catch (\Exception $e) {
            return $this->serverErrorResponse('Failed to delete holiday: ' . $e->getMessage());
        }
    }

    /**
     * Check in to event
     * @PostMapping(path="events/{eventId}/checkin")
     */
    public function checkInEvent(string $eventId): ResponseInterface
    {
        $userId = $this->request->getAttribute('user_id');

        try {
            $attendance = $this->calendarService->checkInEvent($eventId, $userId);
            return $this->successResponse($attendance, 'Checked in to event successfully');
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), null, null, 400);
        }
    }

    /**
     * Check out from event
     * @PostMapping(path="events/{eventId}/checkout")
     */
    public function checkOutEvent(string $eventId): ResponseInterface
    {
        $userId = $this->request->getAttribute('user_id');

        try {
            $this->calendarService->checkOutEvent($eventId, $userId);
            return $this->successResponse(null, 'Checked out from event successfully');
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), null, null, 400);
        }
    }

    /**
     * Mark event attendance
     * @PostMapping(path="events/{eventId}/attendance")
     */
    public function markEventAttendance(string $eventId): ResponseInterface
    {
        $userId = $this->request->getAttribute('user_id');
        $data = $this->request->all();

        if (empty($data['status'])) {
            return $this->errorResponse('Status is required', null, null, 400);
        }

        try {
            $attendance = $this->calendarService->markEventAttendance($eventId, $userId, $data['status'], $data);
            return $this->successResponse($attendance, 'Attendance marked successfully');
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), null, null, 400);
        }
    }

    /**
     * Get event attendance
     * @GetMapping(path="events/{eventId}/attendance")
     */
    public function getEventAttendance(string $eventId): ResponseInterface
    {
        try {
            $attendance = $this->calendarService->getEventAttendance($eventId);
            return $this->successResponse($attendance);
        } catch (\Exception $e) {
            return $this->serverErrorResponse('Failed to retrieve event attendance: ' . $e->getMessage());
        }
    }

    /**
     * Get event attendance statistics
     * @GetMapping(path="events/{eventId}/attendance-stats")
     */
    public function getEventAttendanceStats(string $eventId): ResponseInterface
    {
        try {
            $stats = $this->calendarService->getEventAttendanceStats($eventId);
            return $this->successResponse($stats);
        } catch (\Exception $e) {
            return $this->serverErrorResponse('Failed to retrieve attendance statistics: ' . $e->getMessage());
        }
    }
}
