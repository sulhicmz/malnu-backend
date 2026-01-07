<?php

declare(strict_types=1);

namespace App\Http\Controllers\Calendar;

use App\Http\Controllers\Api\BaseController;
use App\Http\Middleware\JwtMiddleware;
use App\Services\CalendarService;
use DateTime;
use Exception;
use Hyperf\HttpServer\Annotation\Controller;
use Hyperf\HttpServer\Annotation\DeleteMapping;
use Hyperf\HttpServer\Annotation\GetMapping;
use Hyperf\HttpServer\Annotation\Middleware;
use Hyperf\HttpServer\Annotation\PostMapping;
use Hyperf\HttpServer\Annotation\PutMapping;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;

/**
 * @Controller(prefix="api/calendar")
 * @Middleware(JwtMiddleware::class)
 */
class CalendarController extends BaseController
{
    private CalendarService $calendarService;

    public function __construct(
        ContainerInterface $container,
        \Hyperf\HttpServer\Contract\RequestInterface $request,
        \Hyperf\HttpServer\Contract\ResponseInterface $response,
        CalendarService $calendarService
    ) {
        parent::__construct($request, $response, $container);
        $this->calendarService = $calendarService;
    }

    /**
     * Create a new calendar.
     * @PostMapping(path="calendars")
     */
    public function createCalendar(): ResponseInterface
    {
        $data = $this->request->all();

        // Validate required fields
        if (empty($data['name'])) {
            return $this->response->json([
                'success' => false,
                'message' => 'Calendar name is required',
            ])->withStatus(400);
        }

        try {
            $calendar = $this->calendarService->createCalendar($data);

            return $this->response->json([
                'success' => true,
                'data' => $calendar,
                'message' => 'Calendar created successfully',
            ]);
        } catch (Exception $e) {
            return $this->response->json([
                'success' => false,
                'message' => 'Failed to create calendar: ' . $e->getMessage(),
            ])->withStatus(500);
        }
    }

    /**
     * Get calendar by ID.
     * @GetMapping(path="calendars/{id}")
     */
    public function getCalendar(string $id): ResponseInterface
    {
        try {
            $calendar = $this->calendarService->getCalendar($id);

            if (! $calendar) {
                return $this->response->json([
                    'success' => false,
                    'message' => 'Calendar not found',
                ])->withStatus(404);
            }

            return $this->response->json([
                'success' => true,
                'data' => $calendar,
            ]);
        } catch (Exception $e) {
            return $this->response->json([
                'success' => false,
                'message' => 'Failed to retrieve calendar: ' . $e->getMessage(),
            ])->withStatus(500);
        }
    }

    /**
     * Update calendar.
     * @PutMapping(path="calendars/{id}")
     */
    public function updateCalendar(string $id): ResponseInterface
    {
        $data = $this->request->all();

        try {
            $result = $this->calendarService->updateCalendar($id, $data);

            if (! $result) {
                return $this->response->json([
                    'success' => false,
                    'message' => 'Calendar not found',
                ])->withStatus(404);
            }

            $calendar = $this->calendarService->getCalendar($id);

            return $this->response->json([
                'success' => true,
                'data' => $calendar,
                'message' => 'Calendar updated successfully',
            ]);
        } catch (Exception $e) {
            return $this->response->json([
                'success' => false,
                'message' => 'Failed to update calendar: ' . $e->getMessage(),
            ])->withStatus(500);
        }
    }

    /**
     * Delete calendar.
     * @DeleteMapping(path="calendars/{id}")
     */
    public function deleteCalendar(string $id): ResponseInterface
    {
        try {
            $result = $this->calendarService->deleteCalendar($id);

            if (! $result) {
                return $this->response->json([
                    'success' => false,
                    'message' => 'Calendar not found',
                ])->withStatus(404);
            }

            return $this->response->json([
                'success' => true,
                'message' => 'Calendar deleted successfully',
            ]);
        } catch (Exception $e) {
            return $this->response->json([
                'success' => false,
                'message' => 'Failed to delete calendar: ' . $e->getMessage(),
            ])->withStatus(500);
        }
    }

    /**
     * Create a new event.
     * @PostMapping(path="events")
     */
    public function createEvent(): ResponseInterface
    {
        $data = $this->request->all();

        // Validate required fields
        if (empty($data['calendar_id']) || empty($data['title']) || empty($data['start_date']) || empty($data['end_date'])) {
            return $this->response->json([
                'success' => false,
                'message' => 'Calendar ID, title, start date, and end date are required',
            ])->withStatus(400);
        }

        try {
            $event = $this->calendarService->createEvent($data);

            return $this->response->json([
                'success' => true,
                'data' => $event,
                'message' => 'Event created successfully',
            ]);
        } catch (Exception $e) {
            return $this->response->json([
                'success' => false,
                'message' => 'Failed to create event: ' . $e->getMessage(),
            ])->withStatus(500);
        }
    }

    /**
     * Get event by ID.
     * @GetMapping(path="events/{id}")
     */
    public function getEvent(string $id): ResponseInterface
    {
        try {
            $event = $this->calendarService->getEvent($id);

            if (! $event) {
                return $this->response->json([
                    'success' => false,
                    'message' => 'Event not found',
                ])->withStatus(404);
            }

            return $this->response->json([
                'success' => true,
                'data' => $event,
            ]);
        } catch (Exception $e) {
            return $this->response->json([
                'success' => false,
                'message' => 'Failed to retrieve event: ' . $e->getMessage(),
            ])->withStatus(500);
        }
    }

    /**
     * Update event.
     * @PutMapping(path="events/{id}")
     */
    public function updateEvent(string $id): ResponseInterface
    {
        $data = $this->request->all();

        try {
            $result = $this->calendarService->updateEvent($id, $data);

            if (! $result) {
                return $this->response->json([
                    'success' => false,
                    'message' => 'Event not found',
                ])->withStatus(404);
            }

            $event = $this->calendarService->getEvent($id);

            return $this->response->json([
                'success' => true,
                'data' => $event,
                'message' => 'Event updated successfully',
            ]);
        } catch (Exception $e) {
            return $this->response->json([
                'success' => false,
                'message' => 'Failed to update event: ' . $e->getMessage(),
            ])->withStatus(500);
        }
    }

    /**
     * Delete event.
     * @DeleteMapping(path="events/{id}")
     */
    public function deleteEvent(string $id): ResponseInterface
    {
        try {
            $result = $this->calendarService->deleteEvent($id);

            if (! $result) {
                return $this->response->json([
                    'success' => false,
                    'message' => 'Event not found',
                ])->withStatus(404);
            }

            return $this->response->json([
                'success' => true,
                'message' => 'Event deleted successfully',
            ]);
        } catch (Exception $e) {
            return $this->response->json([
                'success' => false,
                'message' => 'Failed to delete event: ' . $e->getMessage(),
            ])->withStatus(500);
        }
    }

    /**
     * Get events by date range.
     * @GetMapping(path="calendars/{calendarId}/events")
     */
    public function getEventsByDateRange(string $calendarId): ResponseInterface
    {
        $startDate = $this->request->query('start_date');
        $endDate = $this->request->query('end_date');
        $category = $this->request->query('category');
        $priority = $this->request->query('priority');

        if (empty($startDate) || empty($endDate)) {
            return $this->response->json([
                'success' => false,
                'message' => 'Start date and end date are required',
            ])->withStatus(400);
        }

        try {
            $carbon = new DateTime($startDate);
            $endDateObj = new DateTime($endDate);

            $filters = [];
            if ($category) {
                $filters['category'] = $category;
            }
            if ($priority) {
                $filters['priority'] = $priority;
            }

            $events = $this->calendarService->getEventsByDateRange($calendarId, $carbon, $endDateObj, $filters);

            return $this->response->json([
                'success' => true,
                'data' => $events,
            ]);
        } catch (Exception $e) {
            return $this->response->json([
                'success' => false,
                'message' => 'Failed to retrieve events: ' . $e->getMessage(),
            ])->withStatus(500);
        }
    }

    /**
     * Register for an event.
     * @PostMapping(path="events/{eventId}/register")
     */
    public function registerForEvent(string $eventId): ResponseInterface
    {
        $userId = $this->request->getAttribute('user_id'); // Assuming JWT middleware sets this
        $data = $this->request->all();

        try {
            $result = $this->calendarService->registerForEvent($eventId, $userId, $data);

            return $this->response->json([
                'success' => true,
                'message' => 'Successfully registered for event',
            ]);
        } catch (Exception $e) {
            return $this->response->json([
                'success' => false,
                'message' => $e->getMessage(),
            ])->withStatus(400);
        }
    }

    /**
     * Share calendar with user.
     * @PostMapping(path="calendars/{calendarId}/share")
     */
    public function shareCalendar(string $calendarId): ResponseInterface
    {
        $data = $this->request->all();

        if (empty($data['user_id']) || empty($data['permission_type'])) {
            return $this->response->json([
                'success' => false,
                'message' => 'User ID and permission type are required',
            ])->withStatus(400);
        }

        try {
            $expiresAt = null;
            if (! empty($data['expires_at'])) {
                $expiresAt = new DateTime($data['expires_at']);
            }

            $result = $this->calendarService->shareCalendar($calendarId, $data['user_id'], $data['permission_type'], $expiresAt);

            return $this->response->json([
                'success' => true,
                'message' => 'Calendar shared successfully',
            ]);
        } catch (Exception $e) {
            return $this->response->json([
                'success' => false,
                'message' => $e->getMessage(),
            ])->withStatus(400);
        }
    }

    /**
     * Book a resource.
     * @PostMapping(path="resources/book")
     */
    public function bookResource(): ResponseInterface
    {
        $data = $this->request->all();

        if (empty($data['resource_type']) || empty($data['resource_id']) || empty($data['start_time']) || empty($data['end_time'])) {
            return $this->response->json([
                'success' => false,
                'message' => 'Resource type, resource ID, start time, and end time are required',
            ])->withStatus(400);
        }

        try {
            $booking = $this->calendarService->bookResource($data);

            return $this->response->json([
                'success' => true,
                'data' => $booking,
                'message' => 'Resource booked successfully',
            ]);
        } catch (Exception $e) {
            return $this->response->json([
                'success' => false,
                'message' => $e->getMessage(),
            ])->withStatus(400);
        }
    }
}
