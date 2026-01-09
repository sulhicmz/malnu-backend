<?php

declare(strict_types=1);

namespace Tests\Feature;

use Tests\TestCase;
use App\Services\CalendarService;
use App\Models\Calendar\Calendar;
use App\Models\Calendar\CalendarEvent;
use App\Models\Calendar\CalendarEventRegistration;
use App\Models\Calendar\CalendarShare;
use App\Models\Calendar\ResourceBooking;
use App\Models\User;

class CalendarSystemTest extends TestCase
{
    private CalendarService $calendarService;

    protected function setUp(): void
    {
        parent::setUp();

        $this->calendarService = new CalendarService();
    }

    public function test_create_calendar_successfully()
    {
        $calendarData = [
            'name' => 'Test Calendar',
            'description' => 'Test Description',
            'color' => '#3b82f6',
            'type' => 'general',
            'is_public' => true,
            'permissions' => ['read', 'write'],
        ];

        $calendar = $this->calendarService->createCalendar($calendarData);

        $this->assertIsArray($calendar);
        $this->assertArrayHasKey('id', $calendar);
        $this->assertEquals('Test Calendar', $calendar['name']);
        $this->assertEquals('general', $calendar['type']);
        $this->assertEquals(true, $calendar['is_public']);
    }

    public function test_get_calendar_by_id()
    {
        $calendarData = ['name' => 'Get Test Calendar'];
        $calendar = $this->calendarService->createCalendar($calendarData);
        $calendarId = $calendar['id'];

        $retrieved = $this->calendarService->getCalendar($calendarId);

        $this->assertIsArray($retrieved);
        $this->assertEquals($calendar['name'], $retrieved['name']);
        $this->assertEquals($calendar['id'], $retrieved['id']);
    }

    public function test_get_nonexistent_calendar_returns_null()
    {
        $retrieved = $this->calendarService->getCalendar('nonexistent-id');

        $this->assertNull($retrieved);
    }

    public function test_update_calendar_successfully()
    {
        $calendar = $this->calendarService->createCalendar(['name' => 'Update Test']);
        $calendarId = $calendar['id'];

        $updated = $this->calendarService->updateCalendar($calendarId, [
            'name' => 'Updated Calendar',
            'description' => 'Updated Description',
        ]);

        $this->assertTrue($updated);

        $retrieved = $this->calendarService->getCalendar($calendarId);
        $this->assertEquals('Updated Calendar', $retrieved['name']);
        $this->assertEquals('Updated Description', $retrieved['description']);
    }

    public function test_delete_calendar_successfully()
    {
        $calendar = $this->calendarService->createCalendar(['name' => 'Delete Test']);
        $calendarId = $calendar['id'];

        $deleted = $this->calendarService->deleteCalendar($calendarId);

        $this->assertTrue($deleted);

        $retrieved = $this->calendarService->getCalendar($calendarId);
        $this->assertNull($retrieved);
    }

    public function test_create_event_successfully()
    {
        $calendar = $this->calendarService->createCalendar(['name' => 'Event Calendar']);
        $calendarId = $calendar['id'];

        $eventData = [
            'calendar_id' => $calendarId,
            'title' => 'Test Event',
            'description' => 'Test Description',
            'start_date' => '2024-01-15 10:00:00',
            'end_date' => '2024-01-15 12:00:00',
            'location' => 'Test Location',
            'category' => 'event',
            'priority' => 'medium',
            'is_all_day' => false,
            'is_recurring' => false,
        ];

        $event = $this->calendarService->createEvent($eventData);

        $this->assertIsArray($event);
        $this->assertArrayHasKey('id', $event);
        $this->assertEquals('Test Event', $event['title']);
        $this->assertEquals('event', $event['category']);
        $this->assertEquals($calendarId, $event['calendar_id']);
    }

    public function test_get_event_by_id()
    {
        $calendar = $this->calendarService->createCalendar(['name' => 'Get Event Calendar']);
        $calendarId = $calendar['id'];

        $eventData = [
            'calendar_id' => $calendarId,
            'title' => 'Get Test Event',
            'start_date' => '2024-01-15 10:00:00',
            'end_date' => '2024-01-15 12:00:00',
        ];
        $event = $this->calendarService->createEvent($eventData);
        $eventId = $event['id'];

        $retrieved = $this->calendarService->getEvent($eventId);

        $this->assertIsArray($retrieved);
        $this->assertEquals($event['title'], $retrieved['title']);
        $this->assertEquals($event['id'], $retrieved['id']);
    }

    public function test_update_event_successfully()
    {
        $calendar = $this->calendarService->createCalendar(['name' => 'Update Event Calendar']);
        $calendarId = $calendar['id'];

        $event = $this->calendarService->createEvent([
            'calendar_id' => $calendarId,
            'title' => 'Update Test Event',
            'start_date' => '2024-01-15 10:00:00',
            'end_date' => '2024-01-15 12:00:00',
        ]);
        $eventId = $event['id'];

        $updated = $this->calendarService->updateEvent($eventId, [
            'title' => 'Updated Event',
            'description' => 'Updated Description',
        ]);

        $this->assertTrue($updated);

        $retrieved = $this->calendarService->getEvent($eventId);
        $this->assertEquals('Updated Event', $retrieved['title']);
        $this->assertEquals('Updated Description', $retrieved['description']);
    }

    public function test_delete_event_successfully()
    {
        $calendar = $this->calendarService->createCalendar(['name' => 'Delete Event Calendar']);
        $calendarId = $calendar['id'];

        $event = $this->calendarService->createEvent([
            'calendar_id' => $calendarId,
            'title' => 'Delete Test Event',
            'start_date' => '2024-01-15 10:00:00',
            'end_date' => '2024-01-15 12:00:00',
        ]);
        $eventId = $event['id'];

        $deleted = $this->calendarService->deleteEvent($eventId);

        $this->assertTrue($deleted);
    }

    public function test_get_events_by_date_range()
    {
        $calendar = $this->calendarService->createCalendar(['name' => 'Date Range Calendar']);
        $calendarId = $calendar['id'];

        $startDate = new \Carbon\Carbon('2024-01-01 00:00:00');
        $endDate = new \Carbon\Carbon('2024-01-31 23:59:59');

        $this->calendarService->createEvent([
            'calendar_id' => $calendarId,
            'title' => 'Event 1',
            'start_date' => '2024-01-15 10:00:00',
            'end_date' => '2024-01-15 12:00:00',
        ]);

        $this->calendarService->createEvent([
            'calendar_id' => $calendarId,
            'title' => 'Event 2',
            'start_date' => '2024-01-20 14:00:00',
            'end_date' => '2024-01-20 16:00:00',
        ]);

        $events = $this->calendarService->getEventsByDateRange($calendarId, $startDate, $endDate);

        $this->assertIsArray($events);
        $this->assertGreaterThanOrEqual(2, count($events));
    }

    public function test_get_events_by_date_range_with_filters()
    {
        $calendar = $this->calendarService->createCalendar(['name' => 'Filter Calendar']);
        $calendarId = $calendar['id'];

        $startDate = new \Carbon\Carbon('2024-01-01 00:00:00');
        $endDate = new \Carbon\Carbon('2024-01-31 23:59:59');

        $this->calendarService->createEvent([
            'calendar_id' => $calendarId,
            'title' => 'Event 1',
            'start_date' => '2024-01-15 10:00:00',
            'end_date' => '2024-01-15 12:00:00',
            'category' => 'exam',
            'priority' => 'high',
        ]);

        $this->calendarService->createEvent([
            'calendar_id' => $calendarId,
            'title' => 'Event 2',
            'start_date' => '2024-01-20 14:00:00',
            'end_date' => '2024-01-20 16:00:00',
            'category' => 'event',
            'priority' => 'low',
        ]);

        $events = $this->calendarService->getEventsByDateRange($calendarId, $startDate, $endDate, [
            'category' => 'exam',
            'priority' => 'high',
        ]);

        $this->assertIsArray($events);
        $this->assertEquals(1, count($events));
        $this->assertEquals('Event 1', $events[0]['title']);
    }

    public function test_register_for_event_successfully()
    {
        $calendar = $this->calendarService->createCalendar(['name' => 'Registration Calendar']);
        $calendarId = $calendar['id'];

        $event = $this->calendarService->createEvent([
            'calendar_id' => $calendarId,
            'title' => 'Registration Test Event',
            'start_date' => '2024-01-15 10:00:00',
            'end_date' => '2024-01-15 12:00:00',
            'requires_registration' => true,
            'max_attendees' => 10,
        ]);
        $eventId = $event['id'];

        $userId = 'test-user-id-1';

        $result = $this->calendarService->registerForEvent($eventId, $userId);

        $this->assertTrue($result);
    }

    public function test_register_for_event_with_max_attendees_throws()
    {
        $calendar = $this->calendarService->createCalendar(['name' => 'Full Event Calendar']);
        $calendarId = $calendar['id'];

        $event = $this->calendarService->createEvent([
            'calendar_id' => $calendarId,
            'title' => 'Full Test Event',
            'start_date' => '2024-01-15 10:00:00',
            'end_date' => '2024-01-15 12:00:00',
            'requires_registration' => true,
            'max_attendees' => 2,
        ]);
        $eventId = $event['id'];

        $this->calendarService->registerForEvent($eventId, 'test-user-id-1');
        $this->calendarService->registerForEvent($eventId, 'test-user-id-2');

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Event is full');
        $this->calendarService->registerForEvent($eventId, 'test-user-id-3');
    }

    public function test_register_for_nonexistent_event_throws()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Event not found');
        $this->calendarService->registerForEvent('nonexistent-id', 'test-user-id');
    }

    public function test_share_calendar_successfully()
    {
        $calendar = $this->calendarService->createCalendar(['name' => 'Share Calendar']);
        $calendarId = $calendar['id'];

        $result = $this->calendarService->shareCalendar($calendarId, 'test-user-id', 'view', null);

        $this->assertTrue($result);
    }

    public function test_share_calendar_with_expiration()
    {
        $calendar = $this->calendarService->createCalendar(['name' => 'Expiring Share Calendar']);
        $calendarId = $calendar['id'];

        $expiresAt = new \Carbon\Carbon('2024-12-31 23:59:59');
        $result = $this->calendarService->shareCalendar($calendarId, 'test-user-id', 'view', $expiresAt);

        $this->assertTrue($result);
    }

    public function test_book_resource_successfully()
    {
        $bookingData = [
            'resource_type' => 'room',
            'resource_id' => 'room-101',
            'start_time' => '2024-01-15 10:00:00',
            'end_time' => '2024-01-15 12:00:00',
            'purpose' => 'Meeting',
            'status' => 'confirmed',
        ];

        $booking = $this->calendarService->bookResource($bookingData);

        $this->assertIsArray($booking);
        $this->assertArrayHasKey('id', $booking);
        $this->assertEquals('room', $booking['resource_type']);
        $this->assertEquals('room-101', $booking['resource_id']);
    }

    public function test_book_resource_with_conflict_throws()
    {
        $bookingData = [
            'resource_type' => 'room',
            'resource_id' => 'room-conflict-101',
            'start_time' => '2024-01-15 10:00:00',
            'end_time' => '2024-01-15 12:00:00',
            'purpose' => 'Meeting 1',
            'status' => 'confirmed',
        ];

        $this->calendarService->bookResource($bookingData);

        $conflictingBooking = [
            'resource_type' => 'room',
            'resource_id' => 'room-conflict-101',
            'start_time' => '2024-01-15 11:00:00',
            'end_time' => '2024-01-15 13:00:00',
            'purpose' => 'Meeting 2',
            'status' => 'confirmed',
        ];

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Resource is already booked for the selected time period');
        $this->calendarService->bookResource($conflictingBooking);
    }

    public function test_get_resource_bookings()
    {
        $bookingData = [
            'resource_type' => 'equipment',
            'resource_id' => 'projector-101',
            'start_time' => '2024-01-15 10:00:00',
            'end_time' => '2024-01-15 12:00:00',
            'purpose' => 'Presentation',
            'status' => 'confirmed',
        ];

        $this->calendarService->bookResource($bookingData);

        $startDate = new \Carbon\Carbon('2024-01-01 00:00:00');
        $endDate = new \Carbon\Carbon('2024-01-31 23:59:59');

        $bookings = $this->calendarService->getResourceBookings('equipment', 'projector-101', $startDate, $endDate);

        $this->assertIsArray($bookings);
        $this->assertGreaterThanOrEqual(1, count($bookings));
    }

    public function test_get_upcoming_events()
    {
        $calendar = $this->calendarService->createCalendar(['name' => 'Upcoming Calendar']);
        $calendarId = $calendar['id'];

        $today = new \Carbon\Carbon();
        $futureDate1 = clone $today->addDays(5);
        $futureDate2 = clone $today->addDays(10);

        $this->calendarService->createEvent([
            'calendar_id' => $calendarId,
            'title' => 'Upcoming Event 1',
            'start_date' => $futureDate1->format('Y-m-d H:i:s'),
            'end_date' => clone $futureDate1->addHours(2)->format('Y-m-d H:i:s'),
        ]);

        $this->calendarService->createEvent([
            'calendar_id' => $calendarId,
            'title' => 'Upcoming Event 2',
            'start_date' => $futureDate2->format('Y-m-d H:i:s'),
            'end_date' => clone $futureDate2->addHours(2)->format('Y-m-d H:i:s'),
        ]);

        $events = $this->calendarService->getUpcomingEvents('test-user-id', 30);

        $this->assertIsArray($events);
        $this->assertGreaterThanOrEqual(2, count($events));
    }
}
