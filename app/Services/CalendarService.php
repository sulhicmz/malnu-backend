<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Calendar\Calendar;
use App\Models\Calendar\CalendarEvent;
use App\Models\Calendar\CalendarEventRegistration;
use App\Models\Calendar\CalendarShare;
use App\Models\Calendar\ResourceBooking;
use App\Models\User;
use Carbon\Carbon;
use Exception;

class CalendarService
{
    /**
     * Create a new calendar
     */
    public function createCalendar(array $data): Calendar
    {
        return Calendar::create($data);
    }

    /**
     * Get calendar by ID
     */
    public function getCalendar(string $id): ?Calendar
    {
        return Calendar::find($id);
    }

    /**
     * Update calendar
     */
    public function updateCalendar(string $id, array $data): bool
    {
        $calendar = Calendar::find($id);
        if (!$calendar) {
            return false;
        }
        
        return $calendar->update($data);
    }

    /**
     * Delete calendar
     */
    public function deleteCalendar(string $id): bool
    {
        $calendar = Calendar::find($id);
        if (!$calendar) {
            return false;
        }
        
        return $calendar->delete();
    }

    /**
     * Create a new calendar event
     */
    public function createEvent(array $data): CalendarEvent
    {
        return CalendarEvent::create($data);
    }

    /**
     * Get event by ID
     */
    public function getEvent(string $id): ?CalendarEvent
    {
        return CalendarEvent::find($id);
    }

    /**
     * Update event
     */
    public function updateEvent(string $id, array $data): bool
    {
        $event = CalendarEvent::find($id);
        if (!$event) {
            return false;
        }
        
        return $event->update($data);
    }

    /**
     * Delete event
     */
    public function deleteEvent(string $id): bool
    {
        $event = CalendarEvent::find($id);
        if (!$event) {
            return false;
        }
        
        return $event->delete();
    }

    /**
     * Get events for a specific date range
     */
    public function getEventsByDateRange(string $calendarId, Carbon $startDate, Carbon $endDate, array $filters = []): array
    {
        $query = CalendarEvent::where('calendar_id', $calendarId)
            ->where(function ($q) use ($startDate, $endDate) {
                $q->whereBetween('start_date', [$startDate, $endDate])
                  ->orWhereBetween('end_date', [$startDate, $endDate])
                  ->orWhere(function ($q) use ($startDate, $endDate) {
                      $q->where('start_date', '<=', $startDate)
                        ->where('end_date', '>=', $endDate);
                  });
            });

        if (!empty($filters['category'])) {
            $query->where('category', $filters['category']);
        }

        if (!empty($filters['priority'])) {
            $query->where('priority', $filters['priority']);
        }

        return $query->orderBy('start_date', 'asc')->get()->toArray();
    }

    /**
     * Get events for a specific user based on permissions
     */
    public function getEventsForUser(string $userId, Carbon $startDate, Carbon $endDate, array $filters = []): array
    {
        $query = CalendarEvent::whereHas('calendar', function ($q) use ($userId) {
            $q->where('is_public', true)
              ->orWhereHas('shares', function ($q) use ($userId) {
                  $q->where('user_id', $userId);
              });
        });

        $query->where(function ($q) use ($startDate, $endDate) {
            $q->whereBetween('start_date', [$startDate, $endDate])
              ->orWhereBetween('end_date', [$startDate, $endDate])
              ->orWhere(function ($q) use ($startDate, $endDate) {
                  $q->where('start_date', '<=', $startDate)
                    ->where('end_date', '>=', $endDate);
              });
        });

        if (!empty($filters['category'])) {
            $query->where('category', $filters['category']);
        }

        if (!empty($filters['priority'])) {
            $query->where('priority', $filters['priority']);
        }

        return $query->orderBy('start_date', 'asc')->get()->toArray();
    }

    /**
     * Register user for an event
     */
    public function registerForEvent(string $eventId, string $userId, array $additionalData = []): bool
    {
        $event = CalendarEvent::find($eventId);
        if (!$event) {
            throw new Exception('Event not found');
        }

        if (!$event->requires_registration) {
            throw new Exception('Event does not require registration');
        }

        if ($event->max_attendees && $this->getRegistrationCount($eventId) >= $event->max_attendees) {
            throw new Exception('Event is full');
        }

        if ($event->registration_deadline && Carbon::now()->greaterThan($event->registration_deadline)) {
            throw new Exception('Registration deadline has passed');
        }

        // Check if user is already registered
        $existingRegistration = CalendarEventRegistration::where('event_id', $eventId)
            ->where('user_id', $userId)
            ->first();

        if ($existingRegistration) {
            throw new Exception('User is already registered for this event');
        }

        CalendarEventRegistration::create([
            'event_id' => $eventId,
            'user_id' => $userId,
            'status' => 'registered',
            'registration_date' => Carbon::now(),
            'additional_data' => $additionalData
        ]);

        return true;
    }

    /**
     * Get registration count for an event
     */
    public function getRegistrationCount(string $eventId): int
    {
        return CalendarEventRegistration::where('event_id', $eventId)->count();
    }

    /**
     * Get registrations for an event
     */
    public function getEventRegistrations(string $eventId): array
    {
        return CalendarEventRegistration::where('event_id', $eventId)->get()->toArray();
    }

    /**
     * Share calendar with user
     */
    public function shareCalendar(string $calendarId, string $userId, string $permissionType, Carbon $expiresAt = null): bool
    {
        $calendar = Calendar::find($calendarId);
        if (!$calendar) {
            throw new Exception('Calendar not found');
        }

        // Check if already shared
        $existingShare = CalendarShare::where('calendar_id', $calendarId)
            ->where('user_id', $userId)
            ->first();

        if ($existingShare) {
            $existingShare->update([
                'permission_type' => $permissionType,
                'expires_at' => $expiresAt
            ]);
        } else {
            CalendarShare::create([
                'calendar_id' => $calendarId,
                'user_id' => $userId,
                'permission_type' => $permissionType,
                'expires_at' => $expiresAt
            ]);
        }

        return true;
    }

    /**
     * Book a resource
     */
    public function bookResource(array $data): ResourceBooking
    {
        // Check for conflicts
        $conflict = ResourceBooking::where('resource_type', $data['resource_type'])
            ->where('resource_id', $data['resource_id'])
            ->where('status', 'confirmed')
            ->where(function ($q) use ($data) {
                $q->whereBetween('start_time', [$data['start_time'], $data['end_time']])
                  ->orWhereBetween('end_time', [$data['start_time'], $data['end_time']])
                  ->orWhere(function ($q) use ($data) {
                      $q->where('start_time', '<=', $data['start_time'])
                        ->where('end_time', '>=', $data['end_time']);
                  });
            })
            ->first();

        if ($conflict) {
            throw new Exception('Resource is already booked for the selected time period');
        }

        return ResourceBooking::create($data);
    }

    /**
     * Get resource bookings by date range
     */
    public function getResourceBookings(string $resourceType, string $resourceId, Carbon $startDate, Carbon $endDate): array
    {
        return ResourceBooking::where('resource_type', $resourceType)
            ->where('resource_id', $resourceId)
            ->where(function ($q) use ($startDate, $endDate) {
                $q->whereBetween('start_time', [$startDate, $endDate])
                  ->orWhereBetween('end_time', [$startDate, $endDate])
                  ->orWhere(function ($q) use ($startDate, $endDate) {
                      $q->where('start_time', '<=', $startDate)
                        ->where('end_time', '>=', $endDate);
                  });
            })
            ->orderBy('start_time', 'asc')
            ->get()
            ->toArray();
    }

    /**
     * Get upcoming events for a user
     */
    public function getUpcomingEvents(string $userId, int $days = 30): array
    {
        $startDate = Carbon::now();
        $endDate = Carbon::now()->addDays($days);

        return $this->getEventsForUser($userId, $startDate, $endDate);
    }
}