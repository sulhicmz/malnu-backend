<?php

declare(strict_types=1);

namespace App\Contracts;

use App\Models\Calendar\Calendar;
use App\Models\Calendar\CalendarEvent;
use App\Models\Calendar\ResourceBooking;
use Carbon\Carbon;

interface CalendarServiceInterface
{
    public function createCalendar(array $data): Calendar;

    public function getCalendar(string $id): ?Calendar;

    public function updateCalendar(string $id, array $data): bool;

    public function deleteCalendar(string $id): bool;

    public function createEvent(array $data): CalendarEvent;

    public function getEvent(string $id): ?CalendarEvent;

    public function updateEvent(string $id, array $data): bool;

    public function deleteEvent(string $id): bool;

    public function getEventsByDateRange(string $calendarId, Carbon $startDate, Carbon $endDate, array $filters = []): array;

    public function getEventsForUser(string $userId, Carbon $startDate, Carbon $endDate, array $filters = []): array;

    public function registerForEvent(string $eventId, string $userId, array $additionalData = []): bool;

    public function getRegistrationCount(string $eventId): int;

    public function getEventRegistrations(string $eventId): array;

    public function shareCalendar(string $calendarId, string $userId, string $permissionType, ?Carbon $expiresAt = null): bool;

    public function bookResource(array $data): ResourceBooking;

    public function getResourceBookings(string $resourceType, string $resourceId, Carbon $startDate, Carbon $endDate): array;

    public function getUpcomingEvents(string $userId, int $days = 30): array;
}
