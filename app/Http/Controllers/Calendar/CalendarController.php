<?php

declare(strict_types=1);

namespace App\Http\Controllers\Calendar;

use App\Http\Controllers\Api\BaseController;
use App\Services\CalendarService;
use Hyperf\HttpServer\Annotation\Controller;
use Hyperf\HttpServer\Annotation\GetMapping;
use Hyperf\HttpServer\Annotation\PostMapping;
use Hyperf\HttpServer\Annotation\PutMapping;
use Hyperf\HttpServer\Annotation\DeleteMapping;
use App\Http\Middleware\JWTMiddleware;
use Hyperf\HttpServer\Contract\RequestInterface;
use Hyperf\HttpServer\Contract\ResponseInterface;
use Hyperf\Di\Annotation\Inject;
use Psr\Container\ContainerInterface;

/**
 * @Controller(prefix="api/calendar")
 * @Middleware(JWTMiddleware::class)
 */
class CalendarController extends BaseController
{
    /**
     * @Inject
     */
    private CalendarService $calendarService;

    public function __construct(
        RequestInterface $request,
        ResponseInterface $response,
        ContainerInterface $container
    ) {
        parent::__construct($request, $response, $container);
    }

    /**
     * Get all calendars
     * @GetMapping(path="calendars")
     */
    public function listCalendars()
    {
        try {
            $calendars = \App\Models\Calendar\Calendar::query()->get()->toArray();

            return $this->successResponse($calendars, 'Calendars retrieved successfully');
        } catch (\Exception $e) {
            return $this->serverErrorResponse('Failed to retrieve calendars: ' . $e->getMessage());
        }
    }

    /**
     * Create a new calendar
     * @PostMapping(path="calendars")
     */
    public function createCalendar()
    {
        $data = $this->request->all();

        if (empty($data['name'])) {
            return $this->validationErrorResponse(['name' => 'Calendar name is required']);
        }

        try {
            $calendar = $this->calendarService->createCalendar($data);

            return $this->successResponse($calendar, 'Calendar created successfully', 201);
        } catch (\Exception $e) {
            return $this->serverErrorResponse('Failed to create calendar: ' . $e->getMessage());
        }
    }

    /**
     * Get calendar by ID
     * @GetMapping(path="calendars/{id}")
     */
    public function getCalendar($id)
    {
        try {
            $calendar = $this->calendarService->getCalendar($id);

            if (!$calendar) {
                return $this->notFoundResponse('Calendar not found');
            }

            return $this->successResponse($calendar, 'Calendar retrieved successfully');
        } catch (\Exception $e) {
            return $this->serverErrorResponse('Failed to retrieve calendar: ' . $e->getMessage());
        }
    }

    /**
     * Update calendar
     * @PutMapping(path="calendars/{id}")
     */
    public function updateCalendar($id)
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
    public function deleteCalendar($id)
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
     * Get events by calendar ID
     * @GetMapping(path="calendars/{calendarId}/events")
     */
    public function getCalendarEvents($calendarId)
    {
        try {
            $events = \App\Models\Calendar\CalendarEvent::query()
                ->where('calendar_id', $calendarId)
                ->get()
                ->toArray();

            return $this->successResponse($events, 'Events retrieved successfully');
        } catch (\Exception $e) {
            return $this->serverErrorResponse('Failed to retrieve events: ' . $e->getMessage());
        }
    }

    /**
     * Get events by date range
     * @GetMapping(path="events/daterange")
     */
    public function getEventsByDateRange()
    {
        $calendarId = $this->request->query('calendar_id');
        $startDate = $this->request->query('start_date');
        $endDate = $this->request->query('end_date');
        $category = $this->request->query('category');
        $priority = $this->request->query('priority');

        if (empty($startDate) || empty($endDate)) {
            return $this->validationErrorResponse([
                'start_date' => 'Start date is required',
                'end_date' => 'End date is required'
            ]);
        }

        if (empty($calendarId)) {
            return $this->validationErrorResponse(['calendar_id' => 'Calendar ID is required']);
        }

        try {
            $carbon = new \Carbon\Carbon($startDate);
            $endDateObj = new \Carbon\Carbon($endDate);

            $filters = [];
            if ($category) {
                $filters['category'] = $category;
            }
            if ($priority) {
                $filters['priority'] = $priority;
            }

            $events = $this->calendarService->getEventsByDateRange($calendarId, $carbon, $endDateObj, $filters);

            return $this->successResponse($events, 'Events retrieved successfully');
        } catch (\Exception $e) {
            return $this->serverErrorResponse('Failed to retrieve events: ' . $e->getMessage());
        }
    }

    /**
     * Create a new event
     * @PostMapping(path="events")
     */
    public function createEvent()
    {
        $data = $this->request->all();

        if (empty($data['calendar_id']) || empty($data['title']) || empty($data['start_date']) || empty($data['end_date'])) {
            return $this->validationErrorResponse([
                'calendar_id' => 'Calendar ID is required',
                'title' => 'Title is required',
                'start_date' => 'Start date is required',
                'end_date' => 'End date is required'
            ]);
        }

        try {
            $event = $this->calendarService->createEvent($data);

            return $this->successResponse($event, 'Event created successfully', 201);
        } catch (\Exception $e) {
            return $this->serverErrorResponse('Failed to create event: ' . $e->getMessage());
        }
    }

    /**
     * Get event by ID
     * @GetMapping(path="events/{id}")
     */
    public function getEvent($id)
    {
        try {
            $event = $this->calendarService->getEvent($id);

            if (!$event) {
                return $this->notFoundResponse('Event not found');
            }

            return $this->successResponse($event, 'Event retrieved successfully');
        } catch (\Exception $e) {
            return $this->serverErrorResponse('Failed to retrieve event: ' . $e->getMessage());
        }
    }

    /**
     * Update event
     * @PutMapping(path="events/{id}")
     */
    public function updateEvent($id)
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
    public function deleteEvent($id)
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
     * Register for an event
     * @PostMapping(path="events/{eventId}/register")
     */
    public function registerForEvent($eventId)
    {
        $userId = $this->request->getAttribute('user_id');
        $data = $this->request->all();

        if (empty($userId)) {
            return $this->unauthorizedResponse('User not authenticated');
        }

        try {
            $this->calendarService->registerForEvent($eventId, $userId, $data);

            return $this->successResponse(null, 'Successfully registered for event');
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 'REGISTRATION_ERROR', null, 400);
        }
    }

    /**
     * Get event registrations
     * @GetMapping(path="events/{eventId}/registrations")
     */
    public function getEventRegistrations($eventId)
    {
        try {
            $registrations = $this->calendarService->getEventRegistrations($eventId);

            return $this->successResponse($registrations, 'Registrations retrieved successfully');
        } catch (\Exception $e) {
            return $this->serverErrorResponse('Failed to retrieve registrations: ' . $e->getMessage());
        }
    }

    /**
     * Get user's upcoming events
     * @GetMapping(path="events/upcoming")
     */
    public function getUpcomingEvents()
    {
        $userId = $this->request->getAttribute('user_id');
        $days = $this->request->query('days', 30);

        if (empty($userId)) {
            return $this->unauthorizedResponse('User not authenticated');
        }

        try {
            $events = $this->calendarService->getUpcomingEvents($userId, (int)$days);

            return $this->successResponse($events, 'Upcoming events retrieved successfully');
        } catch (\Exception $e) {
            return $this->serverErrorResponse('Failed to retrieve upcoming events: ' . $e->getMessage());
        }
    }

    /**
     * Share calendar with user
     * @PostMapping(path="calendars/{calendarId}/share")
     */
    public function shareCalendar($calendarId)
    {
        $data = $this->request->all();

        if (empty($data['user_id']) || empty($data['permission_type'])) {
            return $this->validationErrorResponse([
                'user_id' => 'User ID is required',
                'permission_type' => 'Permission type is required'
            ]);
        }

        try {
            $expiresAt = null;
            if (!empty($data['expires_at'])) {
                $expiresAt = new \Carbon\Carbon($data['expires_at']);
            }

            $this->calendarService->shareCalendar($calendarId, $data['user_id'], $data['permission_type'], $expiresAt);

            return $this->successResponse(null, 'Calendar shared successfully');
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 'SHARE_ERROR', null, 400);
        }
    }

    /**
     * Get calendar shares
     * @GetMapping(path="calendars/{calendarId}/shares")
     */
    public function getCalendarShares($calendarId)
    {
        try {
            $shares = \App\Models\Calendar\CalendarShare::query()
                ->where('calendar_id', $calendarId)
                ->get()
                ->toArray();

            return $this->successResponse($shares, 'Calendar shares retrieved successfully');
        } catch (\Exception $e) {
            return $this->serverErrorResponse('Failed to retrieve calendar shares: ' . $e->getMessage());
        }
    }

    /**
     * Book a resource
     * @PostMapping(path="resources/book")
     */
    public function bookResource()
    {
        $userId = $this->request->getAttribute('user_id');
        $data = $this->request->all();

        if (empty($userId)) {
            return $this->unauthorizedResponse('User not authenticated');
        }

        if (empty($data['resource_type']) || empty($data['resource_id']) || empty($data['start_time']) || empty($data['end_time'])) {
            return $this->validationErrorResponse([
                'resource_type' => 'Resource type is required',
                'resource_id' => 'Resource ID is required',
                'start_time' => 'Start time is required',
                'end_time' => 'End time is required'
            ]);
        }

        try {
            $data['booked_by'] = $userId;

            $booking = $this->calendarService->bookResource($data);

            return $this->successResponse($booking, 'Resource booked successfully', 201);
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 'BOOKING_ERROR', null, 400);
        }
    }

    /**
     * Get resource bookings
     * @GetMapping(path="resources/bookings")
     */
    public function getResourceBookings()
    {
        $resourceType = $this->request->query('resource_type');
        $resourceId = $this->request->query('resource_id');
        $startDate = $this->request->query('start_date');
        $endDate = $this->request->query('end_date');

        if (empty($resourceType) || empty($resourceId) || empty($startDate) || empty($endDate)) {
            return $this->validationErrorResponse([
                'resource_type' => 'Resource type is required',
                'resource_id' => 'Resource ID is required',
                'start_date' => 'Start date is required',
                'end_date' => 'End date is required'
            ]);
        }

        try {
            $carbon = new \Carbon\Carbon($startDate);
            $endDateObj = new \Carbon\Carbon($endDate);

            $bookings = $this->calendarService->getResourceBookings($resourceType, $resourceId, $carbon, $endDateObj);

            return $this->successResponse($bookings, 'Resource bookings retrieved successfully');
        } catch (\Exception $e) {
            return $this->serverErrorResponse('Failed to retrieve resource bookings: ' . $e->getMessage());
        }
    }

    /**
     * Cancel a resource booking
     * @DeleteMapping(path="resources/bookings/{id}")
     */
    public function cancelResourceBooking($id)
    {
        $userId = $this->request->getAttribute('user_id');

        if (empty($userId)) {
            return $this->unauthorizedResponse('User not authenticated');
        }

        try {
            $booking = \App\Models\Calendar\ResourceBooking::query()->find($id);

            if (!$booking) {
                return $this->notFoundResponse('Booking not found');
            }

            if ($booking->booked_by !== $userId) {
                return $this->forbiddenResponse('You can only cancel your own bookings');
            }

            $booking->delete();

            return $this->successResponse(null, 'Booking cancelled successfully');
        } catch (\Exception $e) {
            return $this->serverErrorResponse('Failed to cancel booking: ' . $e->getMessage());
        }
    }
}
