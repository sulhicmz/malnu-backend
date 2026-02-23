<?php

declare(strict_types=1);

namespace App\Contracts;

use App\Models\Calendar\Calendar;
use App\Models\Calendar\CalendarEvent;
use App\Models\Calendar\ResourceBooking;
use Carbon\Carbon;

interface CalendarServiceInterface
{
    /**
     * Create a new calendar.
     */
    public function createCalendar(array $data): Calendar;

    /**
     * Get calendar by ID.
     */
    public function getCalendar(string $id): ?Calendar;

    /**
     * Update calendar.
     */
    public function updateCalendar(string $id, array $data): bool;

    /**
     * Delete calendar.
     */
    public function deleteCalendar(string $id): bool;

    /**
     * Create a new calendar event.
     */
    public function createEvent(array $data): CalendarEvent;

    /**
     * Get event by ID.
     */
    public function getEvent(string $id): ?CalendarEvent;

    /**
     * Update event.
     */
    public function updateEvent(string $id, array $data): bool;

    /**
     * Delete event.
     */
    public function deleteEvent(string $id): bool;

    /**
     * Get events for a specific date range.
     */
    public function getEventsByDateRange(string $calendarId, Carbon $startDate, Carbon $endDate, array $filters = []): array;

    /**
     * Get events for a specific user based on permissions.
     */
    public function getEventsForUser(string $userId, Carbon $startDate, Carbon $endDate, array $filters = []): array;

    /**
     * Register user for an event.
     */
    public function registerForEvent(string $eventId, string $userId, array $additionalData = []): bool;

    /**
     * Get registration count for an event.
     */
    public function getRegistrationCount(string $eventId): int;

    /**
     * Get registrations for an event.
     */
    public function getEventRegistrations(string $eventId): array;

    /**
     * Share calendar with user.
     */
    public function shareCalendar(string $calendarId, string $userId, string $permissionType, ?Carbon $expiresAt = null): bool;

    /**
     * Book a resource.
     */
    public function bookResource(array $data): ResourceBooking;

    /**
     * Get resource bookings by date range.
     */
    public function getResourceBookings(string $resourceType, string $resourceId, Carbon $startDate, Carbon $endDate): array;
}
