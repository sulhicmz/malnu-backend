<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Contracts\CalendarRepositoryInterface;
use App\Models\Calendar\Calendar;
use App\Models\Calendar\CalendarEvent;
use Carbon\Carbon;

class EloquentCalendarRepository implements CalendarRepositoryInterface
{
    private Calendar $calendarModel;
    private CalendarEvent $eventModel;

    public function __construct(Calendar $calendarModel, CalendarEvent $eventModel)
    {
        $this->calendarModel = $calendarModel;
        $this->eventModel = $eventModel;
    }

    public function createCalendar(array $data): Calendar
    {
        return $this->calendarModel->create($data);
    }

    public function findCalendar(string $id): ?Calendar
    {
        return $this->calendarModel->find($id);
    }

    public function updateCalendar(string $id, array $data): bool
    {
        $calendar = $this->findCalendar($id);
        if (!$calendar) {
            return false;
        }

        return $calendar->update($data);
    }

    public function deleteCalendar(string $id): bool
    {
        $calendar = $this->findCalendar($id);
        if (!$calendar) {
            return false;
        }

        return $calendar->delete() > 0;
    }

    public function createEvent(array $data): CalendarEvent
    {
        return $this->eventModel->create($data);
    }

    public function findEvent(string $id): ?CalendarEvent
    {
        return $this->eventModel->find($id);
    }

    public function updateEvent(string $id, array $data): bool
    {
        $event = $this->findEvent($id);
        if (!$event) {
            return false;
        }

        return $event->update($data);
    }

    public function deleteEvent(string $id): bool
    {
        $event = $this->findEvent($id);
        if (!$event) {
            return false;
        }

        return $event->delete() > 0;
    }

    public function getEventsByDateRange(string $calendarId, Carbon $startDate, Carbon $endDate, array $filters = []): array
    {
        $query = $this->eventModel->where('calendar_id', $calendarId)
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

    public function getEventsForUser(string $userId, Carbon $startDate, Carbon $endDate, array $filters = []): array
    {
        $query = $this->eventModel->whereHas('calendar', function ($q) use ($userId) {
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

    public function getUpcomingEvents(string $userId, int $days = 30): array
    {
        $startDate = Carbon::now();
        $endDate = Carbon::now()->addDays($days);

        return $this->getEventsForUser($userId, $startDate, $endDate);
    }
}
