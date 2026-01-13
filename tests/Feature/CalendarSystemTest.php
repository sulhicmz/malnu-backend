<?php

declare(strict_types=1);

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Calendar\Calendar;
use App\Models\Calendar\CalendarEvent;
use App\Models\Calendar\CalendarShare;
use App\Services\CalendarService;

/**
 * Test calendar and event management system
 */
class CalendarSystemTest extends TestCase
{
    private CalendarService $calendarService;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->calendarService = new CalendarService();
    }

    /**
     * Test creating a calendar
     */
    public function test_create_calendar()
    {
        $data = [
            'name' => 'Academic Calendar 2024',
            'description' => 'Main academic calendar for 2024',
            'type' => 'academic',
            'color' => '#3b82f6',
            'is_public' => true
        ];

        $calendar = $this->calendarService->createCalendar($data);

        $this->assertArrayHasKey('id', $calendar);
        $this->assertEquals('Academic Calendar 2024', $calendar['name']);
        $this->assertEquals('academic', $calendar['type']);
        $this->assertTrue($calendar['is_public']);
    }

    /**
     * Test listing calendars for user
     */
    public function test_list_calendars_for_user()
    {
        // Create test calendars
        $this->calendarService->createCalendar([
            'name' => 'Public Calendar',
            'type' => 'general',
            'is_public' => true
        ]);

        $this->calendarService->createCalendar([
            'name' => 'Private Calendar',
            'type' => 'personal',
            'is_public' => false
        ]);

        $calendars = $this->calendarService->getCalendarsForUser();

        $this->assertIsArray($calendars);
        $this->assertGreaterThanOrEqual(2, count($calendars));
    }

    /**
     * Test filtering calendars by type
     */
    public function test_filter_calendars_by_type()
    {
        $this->calendarService->createCalendar([
            'name' => 'Academic Calendar',
            'type' => 'academic',
            'is_public' => true
        ]);

        $this->calendarService->createCalendar([
            'name' => 'Staff Calendar',
            'type' => 'staff',
            'is_public' => true
        ]);

        $academicCalendars = $this->calendarService->getCalendarsForUser(null, 'academic');

        $this->assertIsArray($academicCalendars);
        foreach ($academicCalendars as $calendar) {
            $this->assertEquals('academic', $calendar['type']);
        }
    }

    /**
     * Test getting calendar by ID
     */
    public function test_get_calendar_by_id()
    {
        $data = ['name' => 'Test Calendar', 'type' => 'general'];
        $createdCalendar = $this->calendarService->createCalendar($data);

        $calendar = $this->calendarService->getCalendar($createdCalendar['id']);

        $this->assertIsArray($calendar);
        $this->assertEquals('Test Calendar', $calendar['name']);
    }

    /**
     * Test updating a calendar
     */
    public function test_update_calendar()
    {
        $data = ['name' => 'Original Name', 'type' => 'general'];
        $createdCalendar = $this->calendarService->createCalendar($data);

        $result = $this->calendarService->updateCalendar($createdCalendar['id'], [
            'name' => 'Updated Name',
            'description' => 'Updated description'
        ]);

        $this->assertTrue($result);

        $updatedCalendar = $this->calendarService->getCalendar($createdCalendar['id']);
        $this->assertEquals('Updated Name', $updatedCalendar['name']);
        $this->assertEquals('Updated description', $updatedCalendar['description']);
    }

    /**
     * Test deleting a calendar
     */
    public function test_delete_calendar()
    {
        $data = ['name' => 'Calendar to Delete', 'type' => 'general'];
        $createdCalendar = $this->calendarService->createCalendar($data);
        $calendarId = $createdCalendar['id'];

        $result = $this->calendarService->deleteCalendar($calendarId);

        $this->assertTrue($result);

        $calendar = $this->calendarService->getCalendar($calendarId);
        $this->assertNull($calendar);
    }

    /**
     * Test creating an event
     */
    public function test_create_event()
    {
        $calendarData = ['name' => 'Test Calendar', 'type' => 'general'];
        $calendar = $this->calendarService->createCalendar($calendarData);

        $eventData = [
            'calendar_id' => $calendar['id'],
            'title' => 'Test Event',
            'description' => 'This is a test event',
            'start_date' => date('Y-m-d H:i:s', strtotime('+1 day')),
            'end_date' => date('Y-m-d H:i:s', strtotime('+2 days')),
            'category' => 'event',
            'priority' => 'medium',
            'is_all_day' => false
        ];

        $event = $this->calendarService->createEvent($eventData);

        $this->assertArrayHasKey('id', $event);
        $this->assertEquals('Test Event', $event['title']);
        $this->assertEquals('event', $event['category']);
        $this->assertEquals('medium', $event['priority']);
    }

    /**
     * Test creating recurring event
     */
    public function test_create_recurring_event()
    {
        $calendarData = ['name' => 'Test Calendar', 'type' => 'general'];
        $calendar = $this->calendarService->createCalendar($calendarData);

        $eventData = [
            'calendar_id' => $calendar['id'],
            'title' => 'Weekly Meeting',
            'start_date' => date('Y-m-d H:i:s', strtotime('+1 day')),
            'end_date' => date('Y-m-d H:i:s', strtotime('+2 hours')),
            'is_recurring' => true,
            'recurrence_pattern' => json_encode(['frequency' => 'weekly', 'days' => ['monday', 'wednesday']]),
            'recurrence_end_date' => date('Y-m-d H:i:s', strtotime('+1 month'))
        ];

        $event = $this->calendarService->createEvent($eventData);

        $this->assertArrayHasKey('id', $event);
        $this->assertTrue($event['is_recurring']);
        $this->assertNotEmpty($event['recurrence_pattern']);
    }

    /**
     * Test getting events by date range
     */
    public function test_get_events_by_date_range()
    {
        $calendarData = ['name' => 'Test Calendar', 'type' => 'general'];
        $calendar = $this->calendarService->createCalendar($calendarData);

        $startDate = date('Y-m-d', strtotime('+1 day'));
        $endDate = date('Y-m-d', strtotime('+10 days'));

        $event1 = $this->calendarService->createEvent([
            'calendar_id' => $calendar['id'],
            'title' => 'Event 1',
            'start_date' => date('Y-m-d H:i:s', strtotime('+3 days')),
            'end_date' => date('Y-m-d H:i:s', strtotime('+4 days')),
            'category' => 'event'
        ]);

        $event2 = $this->calendarService->createEvent([
            'calendar_id' => $calendar['id'],
            'title' => 'Event 2',
            'start_date' => date('Y-m-d H:i:s', strtotime('+5 days')),
            'end_date' => date('Y-m-d H:i:s', strtotime('+6 days')),
            'category' => 'exam'
        ]);

        $events = $this->calendarService->getEventsByDateRange(
            $calendar['id'],
            new \DateTime($startDate),
            new \DateTime($endDate),
            []
        );

        $this->assertIsArray($events);
        $this->assertGreaterThanOrEqual(2, count($events));
    }

    /**
     * Test filtering events by category
     */
    public function test_filter_events_by_category()
    {
        $calendarData = ['name' => 'Test Calendar', 'type' => 'general'];
        $calendar = $this->calendarService->createCalendar($calendarData);

        $startDate = new \DateTime();
        $endDate = new \DateTime('+10 days');

        $this->calendarService->createEvent([
            'calendar_id' => $calendar['id'],
            'title' => 'Exam Event',
            'start_date' => date('Y-m-d H:i:s', strtotime('+2 days')),
            'end_date' => date('Y-m-d H:i:s', strtotime('+3 days')),
            'category' => 'exam'
        ]);

        $this->calendarService->createEvent([
            'calendar_id' => $calendar['id'],
            'title' => 'Regular Event',
            'start_date' => date('Y-m-d H:i:s', strtotime('+5 days')),
            'end_date' => date('Y-m-d H:i:s', strtotime('+6 days')),
            'category' => 'event'
        ]);

        $events = $this->calendarService->getEventsByDateRange(
            $calendar['id'],
            $startDate,
            $endDate,
            ['category' => 'exam']
        );

        $this->assertIsArray($events);
        foreach ($events as $event) {
            $this->assertEquals('exam', $event['category']);
        }
    }

    /**
     * Test updating an event
     */
    public function test_update_event()
    {
        $calendarData = ['name' => 'Test Calendar', 'type' => 'general'];
        $calendar = $this->calendarService->createCalendar($calendarData);

        $eventData = [
            'calendar_id' => $calendar['id'],
            'title' => 'Original Event',
            'start_date' => date('Y-m-d H:i:s', strtotime('+1 day')),
            'end_date' => date('Y-m-d H:i:s', strtotime('+2 days'))
        ];

        $event = $this->calendarService->createEvent($eventData);
        $eventId = $event['id'];

        $result = $this->calendarService->updateEvent($eventId, [
            'title' => 'Updated Event',
            'description' => 'Updated event description'
        ]);

        $this->assertTrue($result);

        $updatedEvent = $this->calendarService->getEvent($eventId);
        $this->assertEquals('Updated Event', $updatedEvent['title']);
        $this->assertEquals('Updated event description', $updatedEvent['description']);
    }

    /**
     * Test deleting an event
     */
    public function test_delete_event()
    {
        $calendarData = ['name' => 'Test Calendar', 'type' => 'general'];
        $calendar = $this->calendarService->createCalendar($calendarData);

        $eventData = [
            'calendar_id' => $calendar['id'],
            'title' => 'Event to Delete',
            'start_date' => date('Y-m-d H:i:s', strtotime('+1 day')),
            'end_date' => date('Y-m-d H:i:s', strtotime('+2 days'))
        ];

        $event = $this->calendarService->createEvent($eventData);
        $eventId = $event['id'];

        $result = $this->calendarService->deleteEvent($eventId);

        $this->assertTrue($result);

        $deletedEvent = $this->calendarService->getEvent($eventId);
        $this->assertNull($deletedEvent);
    }

    /**
     * Test event registration
     */
    public function test_register_for_event()
    {
        $calendarData = ['name' => 'Test Calendar', 'type' => 'general'];
        $calendar = $this->calendarService->createCalendar($calendarData);

        $eventData = [
            'calendar_id' => $calendar['id'],
            'title' => 'Registrable Event',
            'start_date' => date('Y-m-d H:i:s', strtotime('+1 day')),
            'end_date' => date('Y-m-d H:i:s', strtotime('+2 days')),
            'requires_registration' => true,
            'max_attendees' => 10,
            'registration_deadline' => date('Y-m-d H:i:s', strtotime('+1 day'))
        ];

        $event = $this->calendarService->createEvent($eventData);
        $eventId = $event['id'];
        $userId = '00000000-0000-0000-0000-000000000001';

        $result = $this->calendarService->registerForEvent($eventId, $userId, []);

        $this->assertTrue($result);

        $registrationCount = $this->calendarService->getRegistrationCount($eventId);
        $this->assertEquals(1, $registrationCount);
    }

    /**
     * Test event registration validation - event full
     */
    public function test_registration_event_full()
    {
        $calendarData = ['name' => 'Test Calendar', 'type' => 'general'];
        $calendar = $this->calendarService->createCalendar($calendarData);

        $eventData = [
            'calendar_id' => $calendar['id'],
            'title' => 'Full Event',
            'start_date' => date('Y-m-d H:i:s', strtotime('+1 day')),
            'end_date' => date('Y-m-d H:i:s', strtotime('+2 days')),
            'requires_registration' => true,
            'max_attendees' => 1
        ];

        $event = $this->calendarService->createEvent($eventData);
        $eventId = $event['id'];
        $userId1 = '00000000-0000-0000-0000-000000000001';
        $userId2 = '00000000-0000-0000-0000-000000000002';

        // First registration should succeed
        $this->calendarService->registerForEvent($eventId, $userId1, []);
        $this->assertTrue(true);

        // Second registration should fail
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Event is full');
        $this->calendarService->registerForEvent($eventId, $userId2, []);
    }

    /**
     * Test event registration validation - deadline passed
     */
    public function test_registration_deadline_passed()
    {
        $calendarData = ['name' => 'Test Calendar', 'type' => 'general'];
        $calendar = $this->calendarService->createCalendar($calendarData);

        $eventData = [
            'calendar_id' => $calendar['id'],
            'title' => 'Past Deadline Event',
            'start_date' => date('Y-m-d H:i:s', strtotime('+1 day')),
            'end_date' => date('Y-m-d H:i:s', strtotime('+2 days')),
            'requires_registration' => true,
            'registration_deadline' => date('Y-m-d H:i:s', strtotime('-1 hour'))
        ];

        $event = $this->calendarService->createEvent($eventData);
        $eventId = $event['id'];
        $userId = '00000000-0000-0000-0000-000000000001';

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Registration deadline has passed');
        $this->calendarService->registerForEvent($eventId, $userId, []);
    }

    /**
     * Test sharing a calendar
     */
    public function test_share_calendar()
    {
        $calendarData = ['name' => 'Shared Calendar', 'type' => 'general'];
        $calendar = $this->calendarService->createCalendar($calendarData);
        $calendarId = $calendar['id'];
        $userId = '00000000-0000-0000-0000-000000000001';

        $result = $this->calendarService->shareCalendar($calendarId, $userId, 'view', null);

        $this->assertTrue($result);

        // Verify share was created
        $shares = CalendarShare::where('calendar_id', $calendarId)
            ->where('user_id', $userId)
            ->get()
            ->toArray();

        $this->assertCount(1, $shares);
        $this->assertEquals('view', $shares[0]['permission_type']);
    }

    /**
     * Test booking a resource
     */
    public function test_book_resource()
    {
        $bookingData = [
            'resource_type' => 'room',
            'resource_id' => 'ROOM-001',
            'start_time' => date('Y-m-d H:i:s', strtotime('+1 day')),
            'end_time' => date('Y-m-d H:i:s', strtotime('+2 hours')),
            'purpose' => 'Meeting'
        ];

        $booking = $this->calendarService->bookResource($bookingData);

        $this->assertArrayHasKey('id', $booking);
        $this->assertEquals('room', $booking['resource_type']);
        $this->assertEquals('ROOM-001', $booking['resource_id']);
    }

    /**
     * Test resource booking conflict detection
     */
    public function test_resource_booking_conflict()
    {
        $bookingData = [
            'resource_type' => 'room',
            'resource_id' => 'ROOM-001',
            'start_time' => date('Y-m-d H:i:s', strtotime('+1 day')),
            'end_time' => date('Y-m-d H:i:s', strtotime('+2 hours')),
            'purpose' => 'First Booking'
        ];

        // First booking should succeed
        $this->calendarService->bookResource($bookingData);

        // Second booking with overlapping time should fail
        $conflictBooking = [
            'resource_type' => 'room',
            'resource_id' => 'ROOM-001',
            'start_time' => date('Y-m-d H:i:s', strtotime('+1 hour 30 minutes')),
            'end_time' => date('Y-m-d H:i:s', strtotime('+2 hours 30 minutes')),
            'purpose' => 'Second Booking'
        ];

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Resource is already booked for the selected time period');
        $this->calendarService->bookResource($conflictBooking);
    }

    /**
     * Test non-existent calendar
     */
    public function test_non_existent_calendar()
    {
        $calendar = $this->calendarService->getCalendar('99999999-9999-9999-9999-999999999999');

        $this->assertNull($calendar);
    }

    /**
     * Test non-existent event
     */
    public function test_non_existent_event()
    {
        $event = $this->calendarService->getEvent('99999999-9999-9999-9999-999999999999');

        $this->assertNull($event);
    }

    /**
     * Test updating non-existent calendar
     */
    public function test_update_non_existent_calendar()
    {
        $result = $this->calendarService->updateCalendar('99999999-9999-9999-9999-999999999999', [
            'name' => 'Updated'
        ]);

        $this->assertFalse($result);
    }

    /**
     * Test deleting non-existent calendar
     */
    public function test_delete_non_existent_calendar()
    {
        $result = $this->calendarService->deleteCalendar('99999999-9999-9999-9999-999999999999');

        $this->assertFalse($result);
    }

    /**
     * Test event with all day flag
     */
    public function test_all_day_event()
    {
        $calendarData = ['name' => 'Test Calendar', 'type' => 'general'];
        $calendar = $this->calendarService->createCalendar($calendarData);

        $eventData = [
            'calendar_id' => $calendar['id'],
            'title' => 'All Day Event',
            'start_date' => date('Y-m-d H:i:s', strtotime('+1 day')),
            'end_date' => date('Y-m-d H:i:s', strtotime('+1 day')),
            'is_all_day' => true
        ];

        $event = $this->calendarService->createEvent($eventData);

        $this->assertTrue($event['is_all_day']);
    }

    /**
     * Test event priority levels
     */
    public function test_event_priority_levels()
    {
        $calendarData = ['name' => 'Test Calendar', 'type' => 'general'];
        $calendar = $this->calendarService->createCalendar($calendarData);

        $priorities = ['low', 'medium', 'high', 'critical'];

        foreach ($priorities as $priority) {
            $event = $this->calendarService->createEvent([
                'calendar_id' => $calendar['id'],
                'title' => "Priority {$priority} Event",
                'start_date' => date('Y-m-d H:i:s', strtotime('+1 day')),
                'end_date' => date('Y-m-d H:i:s', strtotime('+2 days')),
                'priority' => $priority
            ]);

            $this->assertEquals($priority, $event['priority']);
        }
    }
}
