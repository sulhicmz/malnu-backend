<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Calendar\Calendar;
use App\Models\Calendar\CalendarEvent;
use App\Models\Calendar\ResourceBooking;
use App\Models\User;
use App\Services\CalendarService;
use Carbon\Carbon;
use Exception;
use Tests\TestCase;

/**
 * @internal
 * @coversNothing
 */
class CalendarServiceTest extends TestCase
{
    private CalendarService $calendarService;

    private User $user;

    private Calendar $calendar;

    protected function setUp(): void
    {
        parent::setUp();

        $this->calendarService = new CalendarService();

        $this->user = User::create([
            'name' => 'Test User',
            'email' => 'calendar@example.com',
            'password' => password_hash('Password123!', PASSWORD_DEFAULT),
            'role' => 'teacher',
        ]);

        $this->calendar = Calendar::create([
            'name' => 'Test Calendar',
            'owner_id' => $this->user->id,
            'is_public' => false,
        ]);
    }

    public function testCreateCalendarSuccessfully()
    {
        $data = [
            'name' => 'New Calendar',
            'owner_id' => $this->user->id,
            'is_public' => true,
        ];

        $calendar = $this->calendarService->createCalendar($data);

        $this->assertInstanceOf(Calendar::class, $calendar);
        $this->assertEquals('New Calendar', $calendar->name);
        $this->assertTrue($calendar->is_public);
        $this->assertDatabaseHas('calendars', [
            'name' => 'New Calendar',
            'owner_id' => $this->user->id,
        ]);
    }

    public function testGetCalendarByIdReturnsCalendar()
    {
        $retrieved = $this->calendarService->getCalendar($this->calendar->id);

        $this->assertNotNull($retrieved);
        $this->assertEquals($this->calendar->id, $retrieved->id);
        $this->assertEquals('Test Calendar', $retrieved->name);
    }

    public function testGetCalendarWithNonexistentIdReturnsNull()
    {
        $result = $this->calendarService->getCalendar('non-existent-id');

        $this->assertNull($result);
    }

    public function testUpdateCalendarSuccessfully()
    {
        $data = [
            'name' => 'Updated Calendar Name',
            'is_public' => true,
        ];

        $result = $this->calendarService->updateCalendar($this->calendar->id, $data);

        $this->assertTrue($result);
        $this->calendar->refresh();
        $this->assertEquals('Updated Calendar Name', $this->calendar->name);
        $this->assertTrue($this->calendar->is_public);
    }

    public function testUpdateNonexistentCalendarReturnsFalse()
    {
        $result = $this->calendarService->updateCalendar('non-existent-id', [
            'name' => 'Updated',
        ]);

        $this->assertFalse($result);
    }

    public function testDeleteCalendarSuccessfully()
    {
        $calendarId = $this->calendar->id;

        $result = $this->calendarService->deleteCalendar($calendarId);

        $this->assertTrue($result);
        $this->assertDatabaseMissing('calendars', [
            'id' => $calendarId,
        ]);
    }

    public function testDeleteNonexistentCalendarReturnsFalse()
    {
        $result = $this->calendarService->deleteCalendar('non-existent-id');

        $this->assertFalse($result);
    }

    public function testCreateEventSuccessfully()
    {
        $data = [
            'calendar_id' => $this->calendar->id,
            'title' => 'Test Event',
            'description' => 'Test Description',
            'start_date' => Carbon::now()->addDays(1),
            'end_date' => Carbon::now()->addDays(2),
            'category' => 'academic',
            'priority' => 'medium',
        ];

        $event = $this->calendarService->createEvent($data);

        $this->assertInstanceOf(CalendarEvent::class, $event);
        $this->assertEquals('Test Event', $event->title);
        $this->assertEquals('academic', $event->category);
        $this->assertDatabaseHas('calendar_events', [
            'title' => 'Test Event',
            'calendar_id' => $this->calendar->id,
        ]);
    }

    public function testGetEventByIdReturnsEvent()
    {
        $event = CalendarEvent::create([
            'calendar_id' => $this->calendar->id,
            'title' => 'Test Event',
            'start_date' => Carbon::now()->addDays(1),
            'end_date' => Carbon::now()->addDays(2),
        ]);

        $retrieved = $this->calendarService->getEvent($event->id);

        $this->assertNotNull($retrieved);
        $this->assertEquals($event->id, $retrieved->id);
        $this->assertEquals('Test Event', $retrieved->title);
    }

    public function testGetEventWithNonexistentIdReturnsNull()
    {
        $result = $this->calendarService->getEvent('non-existent-id');

        $this->assertNull($result);
    }

    public function testUpdateEventSuccessfully()
    {
        $event = CalendarEvent::create([
            'calendar_id' => $this->calendar->id,
            'title' => 'Original Title',
            'start_date' => Carbon::now()->addDays(1),
            'end_date' => Carbon::now()->addDays(2),
        ]);

        $data = [
            'title' => 'Updated Title',
            'description' => 'Updated Description',
        ];

        $result = $this->calendarService->updateEvent($event->id, $data);

        $this->assertTrue($result);
        $event->refresh();
        $this->assertEquals('Updated Title', $event->title);
        $this->assertEquals('Updated Description', $event->description);
    }

    public function testUpdateNonexistentEventReturnsFalse()
    {
        $result = $this->calendarService->updateEvent('non-existent-id', [
            'title' => 'Updated',
        ]);

        $this->assertFalse($result);
    }

    public function testDeleteEventSuccessfully()
    {
        $event = CalendarEvent::create([
            'calendar_id' => $this->calendar->id,
            'title' => 'Event to Delete',
            'start_date' => Carbon::now()->addDays(1),
            'end_date' => Carbon::now()->addDays(2),
        ]);

        $eventId = $event->id;

        $result = $this->calendarService->deleteEvent($eventId);

        $this->assertTrue($result);
        $this->assertDatabaseMissing('calendar_events', [
            'id' => $eventId,
        ]);
    }

    public function testDeleteNonexistentEventReturnsFalse()
    {
        $result = $this->calendarService->deleteEvent('non-existent-id');

        $this->assertFalse($result);
    }

    public function testGetEventsByDateRange()
    {
        $event1 = CalendarEvent::create([
            'calendar_id' => $this->calendar->id,
            'title' => 'Event 1',
            'start_date' => Carbon::now()->addDay(),
            'end_date' => Carbon::now()->addDays(2),
        ]);

        $event2 = CalendarEvent::create([
            'calendar_id' => $this->calendar->id,
            'title' => 'Event 2',
            'start_date' => Carbon::now()->addDays(5),
            'end_date' => Carbon::now()->addDays(6),
        ]);

        $startDate = Carbon::now();
        $endDate = Carbon::now()->addDays(7);

        $events = $this->calendarService->getEventsByDateRange(
            $this->calendar->id,
            $startDate,
            $endDate
        );

        $this->assertIsArray($events);
        $this->assertCount(2, $events);
    }

    public function testGetEventsByDateRangeWithCategoryFilter()
    {
        CalendarEvent::create([
            'calendar_id' => $this->calendar->id,
            'title' => 'Academic Event',
            'start_date' => Carbon::now()->addDay(),
            'end_date' => Carbon::now()->addDays(2),
            'category' => 'academic',
        ]);

        CalendarEvent::create([
            'calendar_id' => $this->calendar->id,
            'title' => 'Event Event',
            'start_date' => Carbon::now()->addDay(),
            'end_date' => Carbon::now()->addDays(2),
            'category' => 'event',
        ]);

        $startDate = Carbon::now();
        $endDate = Carbon::now()->addDays(7);

        $events = $this->calendarService->getEventsByDateRange(
            $this->calendar->id,
            $startDate,
            $endDate,
            ['category' => 'academic']
        );

        $this->assertCount(1, $events);
        $this->assertEquals('Academic Event', $events[0]['title']);
    }

    public function testGetEventsForUserFromPublicCalendar()
    {
        $publicCalendar = Calendar::create([
            'name' => 'Public Calendar',
            'owner_id' => $this->user->id,
            'is_public' => true,
        ]);

        CalendarEvent::create([
            'calendar_id' => $publicCalendar->id,
            'title' => 'Public Event',
            'start_date' => Carbon::now()->addDay(),
            'end_date' => Carbon::now()->addDays(2),
        ]);

        $startDate = Carbon::now();
        $endDate = Carbon::now()->addDays(7);

        $events = $this->calendarService->getEventsForUser(
            $this->user->id,
            $startDate,
            $endDate
        );

        $this->assertIsArray($events);
        $this->assertNotEmpty($events);
    }

    public function testRegisterForEventSuccessfully()
    {
        $event = CalendarEvent::create([
            'calendar_id' => $this->calendar->id,
            'title' => 'Event with Registration',
            'start_date' => Carbon::now()->addDays(7),
            'end_date' => Carbon::now()->addDays(8),
            'requires_registration' => true,
            'max_attendees' => 10,
            'registration_deadline' => Carbon::now()->addDays(5),
        ]);

        $result = $this->calendarService->registerForEvent($event->id, $this->user->id);

        $this->assertTrue($result);
        $this->assertDatabaseHas('calendar_event_registrations', [
            'event_id' => $event->id,
            'user_id' => $this->user->id,
            'status' => 'registered',
        ]);
    }

    public function testRegisterForNonexistentEventThrowsException()
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Event not found');

        $this->calendarService->registerForEvent('non-existent-id', $this->user->id);
    }

    public function testRegisterForEventNotRequiringRegistrationThrowsException()
    {
        $event = CalendarEvent::create([
            'calendar_id' => $this->calendar->id,
            'title' => 'No Registration Event',
            'start_date' => Carbon::now()->addDays(7),
            'end_date' => Carbon::now()->addDays(8),
            'requires_registration' => false,
        ]);

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Event does not require registration');

        $this->calendarService->registerForEvent($event->id, $this->user->id);
    }

    public function testRegisterForFullEventThrowsException()
    {
        $event = CalendarEvent::create([
            'calendar_id' => $this->calendar->id,
            'title' => 'Full Event',
            'start_date' => Carbon::now()->addDays(7),
            'end_date' => Carbon::now()->addDays(8),
            'requires_registration' => true,
            'max_attendees' => 1,
            'registration_deadline' => Carbon::now()->addDays(5),
        ]);

        $otherUser = User::create([
            'name' => 'Other User',
            'email' => 'other@example.com',
            'password' => password_hash('Password123!', PASSWORD_DEFAULT),
        ]);

        $this->calendarService->registerForEvent($event->id, $otherUser->id);

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Event is full');

        $this->calendarService->registerForEvent($event->id, $this->user->id);
    }

    public function testRegisterAfterDeadlineThrowsException()
    {
        $event = CalendarEvent::create([
            'calendar_id' => $this->calendar->id,
            'title' => 'Past Deadline Event',
            'start_date' => Carbon::now()->addDays(7),
            'end_date' => Carbon::now()->addDays(8),
            'requires_registration' => true,
            'registration_deadline' => Carbon::now()->subDay(),
        ]);

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Registration deadline has passed');

        $this->calendarService->registerForEvent($event->id, $this->user->id);
    }

    public function testRegisterTwiceForSameEventThrowsException()
    {
        $event = CalendarEvent::create([
            'calendar_id' => $this->calendar->id,
            'title' => 'Double Registration Event',
            'start_date' => Carbon::now()->addDays(7),
            'end_date' => Carbon::now()->addDays(8),
            'requires_registration' => true,
            'max_attendees' => 10,
            'registration_deadline' => Carbon::now()->addDays(5),
        ]);

        $this->calendarService->registerForEvent($event->id, $this->user->id);

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('User is already registered for this event');

        $this->calendarService->registerForEvent($event->id, $this->user->id);
    }

    public function testGetRegistrationCount()
    {
        $event = CalendarEvent::create([
            'calendar_id' => $this->calendar->id,
            'title' => 'Count Event',
            'start_date' => Carbon::now()->addDays(7),
            'end_date' => Carbon::now()->addDays(8),
            'requires_registration' => true,
        ]);

        $otherUser = User::create([
            'name' => 'Other User',
            'email' => 'count@example.com',
            'password' => password_hash('Password123!', PASSWORD_DEFAULT),
        ]);

        $this->calendarService->registerForEvent($event->id, $this->user->id);
        $this->calendarService->registerForEvent($event->id, $otherUser->id);

        $count = $this->calendarService->getRegistrationCount($event->id);

        $this->assertEquals(2, $count);
    }

    public function testGetEventRegistrations()
    {
        $event = CalendarEvent::create([
            'calendar_id' => $this->calendar->id,
            'title' => 'Registrations Event',
            'start_date' => Carbon::now()->addDays(7),
            'end_date' => Carbon::now()->addDays(8),
            'requires_registration' => true,
        ]);

        $otherUser = User::create([
            'name' => 'Other User',
            'email' => 'reg@example.com',
            'password' => password_hash('Password123!', PASSWORD_DEFAULT),
        ]);

        $this->calendarService->registerForEvent($event->id, $this->user->id);
        $this->calendarService->registerForEvent($event->id, $otherUser->id);

        $registrations = $this->calendarService->getEventRegistrations($event->id);

        $this->assertIsArray($registrations);
        $this->assertCount(2, $registrations);
    }

    public function testShareCalendarSuccessfully()
    {
        $otherUser = User::create([
            'name' => 'Share User',
            'email' => 'share@example.com',
            'password' => password_hash('Password123!', PASSWORD_DEFAULT),
        ]);

        $result = $this->calendarService->shareCalendar(
            $this->calendar->id,
            $otherUser->id,
            'read'
        );

        $this->assertTrue($result);
        $this->assertDatabaseHas('calendar_shares', [
            'calendar_id' => $this->calendar->id,
            'user_id' => $otherUser->id,
            'permission_type' => 'read',
        ]);
    }

    public function testShareCalendarWithNonexistentCalendarThrowsException()
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Calendar not found');

        $this->calendarService->shareCalendar('non-existent-id', $this->user->id, 'read');
    }

    public function testShareCalendarUpdatesExistingShare()
    {
        $otherUser = User::create([
            'name' => 'Update Share User',
            'email' => 'updateshare@example.com',
            'password' => password_hash('Password123!', PASSWORD_DEFAULT),
        ]);

        $this->calendarService->shareCalendar($this->calendar->id, $otherUser->id, 'read');

        $result = $this->calendarService->shareCalendar(
            $this->calendar->id,
            $otherUser->id,
            'write'
        );

        $this->assertTrue($result);
        $this->assertDatabaseHas('calendar_shares', [
            'calendar_id' => $this->calendar->id,
            'user_id' => $otherUser->id,
            'permission_type' => 'write',
        ]);
        $this->assertDatabaseMissing('calendar_shares', [
            'calendar_id' => $this->calendar->id,
            'user_id' => $otherUser->id,
            'permission_type' => 'read',
        ]);
    }

    public function testBookResourceSuccessfully()
    {
        $data = [
            'resource_type' => 'classroom',
            'resource_id' => 'ROOM-101',
            'booked_by' => $this->user->id,
            'start_time' => Carbon::now()->addDays(1)->setHour(9)->setMinute(0),
            'end_time' => Carbon::now()->addDays(1)->setHour(10)->setMinute(0),
            'purpose' => 'Math Class',
            'status' => 'confirmed',
        ];

        $booking = $this->calendarService->bookResource($data);

        $this->assertInstanceOf(ResourceBooking::class, $booking);
        $this->assertEquals('classroom', $booking->resource_type);
        $this->assertEquals('ROOM-101', $booking->resource_id);
        $this->assertDatabaseHas('resource_bookings', [
            'resource_type' => 'classroom',
            'resource_id' => 'ROOM-101',
            'status' => 'confirmed',
        ]);
    }

    public function testBookResourceWithConflictThrowsException()
    {
        $startTime = Carbon::now()->addDays(1)->setHour(9)->setMinute(0);
        $endTime = Carbon::now()->addDays(1)->setHour(10)->setMinute(0);

        ResourceBooking::create([
            'resource_type' => 'classroom',
            'resource_id' => 'ROOM-101',
            'booked_by' => $this->user->id,
            'start_time' => $startTime,
            'end_time' => $endTime,
            'purpose' => 'Existing Booking',
            'status' => 'confirmed',
        ]);

        $conflictData = [
            'resource_type' => 'classroom',
            'resource_id' => 'ROOM-101',
            'booked_by' => $this->user->id,
            'start_time' => $startTime->copy()->addMinutes(30),
            'end_time' => $endTime->copy()->addMinutes(30),
            'purpose' => 'Conflict Booking',
            'status' => 'confirmed',
        ];

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Resource is already booked for selected time period');

        $this->calendarService->bookResource($conflictData);
    }

    public function testGetResourceBookingsByDateRange()
    {
        $booking = ResourceBooking::create([
            'resource_type' => 'classroom',
            'resource_id' => 'ROOM-101',
            'booked_by' => $this->user->id,
            'start_time' => Carbon::now()->addDays(1)->setHour(9),
            'end_time' => Carbon::now()->addDays(1)->setHour(10),
            'purpose' => 'Test Booking',
            'status' => 'confirmed',
        ]);

        $startDate = Carbon::now();
        $endDate = Carbon::now()->addDays(7);

        $bookings = $this->calendarService->getResourceBookings(
            'classroom',
            'ROOM-101',
            $startDate,
            $endDate
        );

        $this->assertIsArray($bookings);
        $this->assertCount(1, $bookings);
        $this->assertEquals($booking->id, $bookings[0]['id']);
    }

    public function testGetUpcomingEvents()
    {
        CalendarEvent::create([
            'calendar_id' => $this->calendar->id,
            'title' => 'Upcoming Event',
            'start_date' => Carbon::now()->addDays(5),
            'end_date' => Carbon::now()->addDays(6),
        ]);

        CalendarEvent::create([
            'calendar_id' => $this->calendar->id,
            'title' => 'Future Event',
            'start_date' => Carbon::now()->addDays(25),
            'end_date' => Carbon::now()->addDays(26),
        ]);

        $this->calendar->update(['is_public' => true]);

        $upcoming = $this->calendarService->getUpcomingEvents($this->user->id, 30);

        $this->assertIsArray($upcoming);
        $this->assertGreaterThanOrEqual(2, count($upcoming));
    }
}
