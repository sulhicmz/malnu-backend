<?php

declare(strict_types=1);

namespace App\Contracts;

use App\Models\Calendar\Calendar;
use App\Models\Calendar\CalendarEvent;
use App\Models\Calendar\ResourceBooking;
use Carbon\Carbon;

/**
 * Repository interface for Calendar data access operations.
 *
 * This interface provides an abstraction layer for calendar data operations,
 * enabling dependency injection, mocking in tests, and potential
 * data source switching (Eloquent, MongoDB, Redis, etc.).
 */
interface CalendarRepositoryInterface
{
    /**
     * Create a new calendar.
     *
     * @param array $data Calendar data
     * @return Calendar The created calendar model
     */
    public function createCalendar(array $data): Calendar;

    /**
     * Find a calendar by ID.
     *
     * @param string $id The calendar ID
     * @return Calendar|null The calendar model or null if not found
     */
    public function findCalendar(string $id): ?Calendar;

    /**
     * Update an existing calendar.
     *
     * @param string $id The calendar ID
     * @param array $data The data to update
     * @return bool True if update was successful, false otherwise
     */
    public function updateCalendar(string $id, array $data): bool;

    /**
     * Delete a calendar by ID.
     *
     * @param string $id The calendar ID
     * @return bool True if deletion was successful, false otherwise
     */
    public function deleteCalendar(string $id): bool;

    /**
     * Create a new calendar event.
     *
     * @param array $data Event data
     * @return CalendarEvent The created event model
     */
    public function createEvent(array $data): CalendarEvent;

    /**
     * Find an event by ID.
     *
     * @param string $id The event ID
     * @return CalendarEvent|null The event model or null if not found
     */
    public function findEvent(string $id): ?CalendarEvent;

    /**
     * Update an existing event.
     *
     * @param string $id The event ID
     * @param array $data The data to update
     * @return bool True if update was successful, false otherwise
     */
    public function updateEvent(string $id, array $data): bool;

    /**
     * Delete an event by ID.
     *
     * @param string $id The event ID
     * @return bool True if deletion was successful, false otherwise
     */
    public function deleteEvent(string $id): bool;

    /**
     * Get events for a specific calendar within a date range.
     *
     * @param string $calendarId The calendar ID
     * @param Carbon $startDate Start of date range
     * @param Carbon $endDate End of date range
     * @param array $filters Optional filters (category, priority)
     * @return array Array of events matching the criteria
     */
    public function getEventsByDateRange(string $calendarId, Carbon $startDate, Carbon $endDate, array $filters = []): array;

    /**
     * Get events visible to a specific user based on permissions.
     *
     * @param string $userId The user ID
     * @param Carbon $startDate Start of date range
     * @param Carbon $endDate End of date range
     * @param array $filters Optional filters (category, priority)
     * @return array Array of events visible to the user
     */
    public function getEventsForUser(string $userId, Carbon $startDate, Carbon $endDate, array $filters = []): array;

    /**
     * Get upcoming events for a user (next N days).
     *
     * @param string $userId The user ID
     * @param int $days Number of days to look ahead (default: 30)
     * @return array Array of upcoming events
     */
    public function getUpcomingEvents(string $userId, int $days = 30): array;
}
