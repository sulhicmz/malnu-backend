<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Calendar\Calendar;
use App\Models\Calendar\CalendarEvent;
use App\Models\Calendar\CalendarEventRegistration;
use App\Models\Calendar\CalendarShare;
use App\Models\Calendar\ResourceBooking;
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

    protected function setUp(): void
    {
        parent::setUp();
        $this->calendarService = new CalendarService();
    }

    public function testCreateCalendar()
    {
        $data = [
            'id' => $this->generateUuid(),
            'name' => 'Test Calendar',
            'description' => 'Test calendar description',
            'is_public' => true,
            'owner_id' => $this->generateUuid(),
        ];

        $calendar = $this->calendarService->createCalendar($data);

        $this->assertInstanceOf(Calendar::class, $calendar);
        $this->assertEquals('Test Calendar', $calendar->name);
        $this->assertEquals('Test calendar description', $calendar->description);
        $this->assertTrue($calendar->is_public);
    }

    public function testGetCalendarById()
    {
        $calendarId = $this->generateUuid();
        Calendar::create([
            'id' => $calendarId,
            'name' => 'Test Calendar',
            'description' => 'Test description',
            'is_public' => true,
            'owner_id' => $this->generateUuid(),
        ]);

        $calendar = $this->calendarService->getCalendar($calendarId);

        $this->assertNotNull($calendar);
        $this->assertEquals($calendarId, $calendar->id);
        $this->assertEquals('Test Calendar', $calendar->name);
    }

    public function testGetNonexistentCalendarReturnsNull()
    {
        $nonExistentId = '00000000-0000-0000-0000-000000000000';
        $calendar = $this->calendarService->getCalendar($nonExistentId);

        $this->assertNull($calendar);
    }

    public function testUpdateCalendar()
    {
        $calendarId = $this->generateUuid();
        Calendar::create([
            'id' => $calendarId,
            'name' => 'Original Name',
            'description' => 'Original description',
            'is_public' => false,
            'owner_id' => $this->generateUuid(),
        ]);

        $result = $this->calendarService->updateCalendar($calendarId, [
            'name' => 'Updated Name',
            'description' => 'Updated description',
        ]);

        $this->assertTrue($result);

        $calendar = Calendar::find($calendarId);
        $this->assertEquals('Updated Name', $calendar->name);
        $this->assertEquals('Updated description', $calendar->description);
    }

    public function testUpdateNonexistentCalendarReturnsFalse()
    {
        $result = $this->calendarService->updateCalendar(
            '00000000-0000-0000-0000-000000000000',
            ['name' => 'New Name']
        );

        $this->assertFalse($result);
    }

    public function testDeleteCalendar()
    {
        $calendarId = $this->generateUuid();
        Calendar::create([
            'id' => $calendarId,
            'name' => 'To Delete',
            'description' => 'Will be deleted',
            'is_public' => true,
            'owner_id' => $this->generateUuid(),
        ]);

        $result = $this->calendarService->deleteCalendar($calendarId);

        $this->assertTrue($result);
        $this->assertNull(Calendar::find($calendarId));
    }

    public function testDeleteNonexistentCalendarReturnsFalse()
    {
        $result = $this->calendarService->deleteCalendar('00000000-0000-0000-0000-000000000000');

        $this->assertFalse($result);
    }

    public function testCreateEvent()
    {
        $calendarId = $this->generateUuid();
        $this->createCalendar($calendarId);

        $data = [
            'id' => $this->generateUuid(),
            'calendar_id' => $calendarId,
            'title' => 'Test Event',
            'description' => 'Event description',
            'start_date' => Carbon::now()->addDays(1),
            'end_date' => Carbon::now()->addDays(1)->addHours(2),
            'category' => 'meeting',
            'priority' => 'medium',
            'requires_registration' => false,
        ];

        $event = $this->calendarService->createEvent($data);

        $this->assertInstanceOf(CalendarEvent::class, $event);
        $this->assertEquals('Test Event', $event->title);
        $this->assertEquals('Event description', $event->description);
        $this->assertEquals('meeting', $event->category);
        $this->assertEquals('medium', $event->priority);
    }

    public function testGetEventsByDateRangeIncludesOverlappingEvents()
    {
        $calendarId = $this->generateUuid();
        $this->createCalendar($calendarId);

        $startDate = Carbon::now();
        $endDate = Carbon::now()->addDays(7);

        CalendarEvent::create([
            'id' => $this->generateUuid(),
            'calendar_id' => $calendarId,
            'title' => 'Event 1',
            'start_date' => $startDate->copy()->addDays(1),
            'end_date' => $startDate->copy()->addDays(3),
            'category' => 'meeting',
            'priority' => 'low',
        ]);

        CalendarEvent::create([
            'id' => $this->generateUuid(),
            'calendar_id' => $calendarId,
            'title' => 'Event 2',
            'start_date' => $startDate->copy()->addDays(2),
            'end_date' => $startDate->copy()->addDays(5),
            'category' => 'event',
            'priority' => 'high',
        ]);

        CalendarEvent::create([
            'id' => $this->generateUuid(),
            'calendar_id' => $calendarId,
            'title' => 'Event 3',
            'start_date' => $startDate->copy()->addDays(10),
            'end_date' => $startDate->copy()->addDays(12),
            'category' => 'event',
            'priority' => 'medium',
        ]);

        $events = $this->calendarService->getEventsByDateRange($calendarId, $startDate, $endDate);

        $this->assertCount(2, $events);
        $this->assertEquals('Event 1', $events[0]['title']);
        $this->assertEquals('Event 2', $events[1]['title']);
    }

    public function testGetEventsByDateRangeWithCategoryFilter()
    {
        $calendarId = $this->generateUuid();
        $this->createCalendar($calendarId);

        $startDate = Carbon::now();
        $endDate = Carbon::now()->addDays(10);

        CalendarEvent::create([
            'id' => $this->generateUuid(),
            'calendar_id' => $calendarId,
            'title' => 'Meeting Event',
            'start_date' => $startDate->copy()->addDays(1),
            'end_date' => $startDate->copy()->addDays(2),
            'category' => 'meeting',
            'priority' => 'high',
        ]);

        CalendarEvent::create([
            'id' => $this->generateUuid(),
            'calendar_id' => $calendarId,
            'title' => 'Event Event',
            'start_date' => $startDate->copy()->addDays(3),
            'end_date' => $startDate->copy()->addDays(4),
            'category' => 'event',
            'priority' => 'medium',
        ]);

        $events = $this->calendarService->getEventsByDateRange(
            $calendarId,
            $startDate,
            $endDate,
            ['category' => 'meeting']
        );

        $this->assertCount(1, $events);
        $this->assertEquals('Meeting Event', $events[0]['title']);
    }

    public function testGetEventsByDateRangeWithPriorityFilter()
    {
        $calendarId = $this->generateUuid();
        $this->createCalendar($calendarId);

        $startDate = Carbon::now();
        $endDate = Carbon::now()->addDays(10);

        CalendarEvent::create([
            'id' => $this->generateUuid(),
            'calendar_id' => $calendarId,
            'title' => 'High Priority Event',
            'start_date' => $startDate->copy()->addDays(1),
            'end_date' => $startDate->copy()->addDays(2),
            'category' => 'meeting',
            'priority' => 'high',
        ]);

        CalendarEvent::create([
            'id' => $this->generateUuid(),
            'calendar_id' => $calendarId,
            'title' => 'Low Priority Event',
            'start_date' => $startDate->copy()->addDays(3),
            'end_date' => $startDate->copy()->addDays(4),
            'category' => 'event',
            'priority' => 'low',
        ]);

        $events = $this->calendarService->getEventsByDateRange(
            $calendarId,
            $startDate,
            $endDate,
            ['priority' => 'high']
        );

        $this->assertCount(1, $events);
        $this->assertEquals('High Priority Event', $events[0]['title']);
    }

    public function testRegisterForEventSucceeds()
    {
        $calendarId = $this->generateUuid();
        $eventId = $this->generateUuid();
        $userId = $this->generateUuid();

        $this->createCalendar($calendarId);

        CalendarEvent::create([
            'id' => $eventId,
            'calendar_id' => $calendarId,
            'title' => 'Test Event',
            'start_date' => Carbon::now()->addDays(10),
            'end_date' => Carbon::now()->addDays(11),
            'category' => 'event',
            'priority' => 'medium',
            'requires_registration' => true,
            'max_attendees' => 10,
            'registration_deadline' => Carbon::now()->addDays(5),
        ]);

        $result = $this->calendarService->registerForEvent($eventId, $userId);

        $this->assertTrue($result);

        $registration = CalendarEventRegistration::where('event_id', $eventId)
            ->where('user_id', $userId)
            ->first();
        $this->assertNotNull($registration);
        $this->assertEquals('registered', $registration->status);
    }

    public function testRegisterForNonexistentEventThrowsException()
    {
        $nonExistentEventId = '00000000-0000-0000-0000-000000000000';
        $userId = $this->generateUuid();

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Event not found');

        $this->calendarService->registerForEvent($nonExistentEventId, $userId);
    }

    public function testRegisterForEventWithoutRegistrationThrowsException()
    {
        $calendarId = $this->generateUuid();
        $eventId = $this->generateUuid();
        $userId = $this->generateUuid();

        $this->createCalendar($calendarId);

        CalendarEvent::create([
            'id' => $eventId,
            'calendar_id' => $calendarId,
            'title' => 'Test Event',
            'start_date' => Carbon::now()->addDays(10),
            'end_date' => Carbon::now()->addDays(11),
            'category' => 'event',
            'priority' => 'medium',
            'requires_registration' => false,
            'max_attendees' => 10,
        ]);

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Event does not require registration');

        $this->calendarService->registerForEvent($eventId, $userId);
    }

    public function testRegisterForFullEventThrowsException()
    {
        $calendarId = $this->generateUuid();
        $eventId = $this->generateUuid();
        $userId = $this->generateUuid();

        $this->createCalendar($calendarId);

        CalendarEvent::create([
            'id' => $eventId,
            'calendar_id' => $calendarId,
            'title' => 'Test Event',
            'start_date' => Carbon::now()->addDays(10),
            'end_date' => Carbon::now()->addDays(11),
            'category' => 'event',
            'priority' => 'medium',
            'requires_registration' => true,
            'max_attendees' => 2,
            'registration_deadline' => Carbon::now()->addDays(5),
        ]);

        CalendarEventRegistration::create([
            'id' => $this->generateUuid(),
            'event_id' => $eventId,
            'user_id' => $this->generateUuid(),
            'status' => 'registered',
            'registration_date' => Carbon::now(),
        ]);

        CalendarEventRegistration::create([
            'id' => $this->generateUuid(),
            'event_id' => $eventId,
            'user_id' => $this->generateUuid(),
            'status' => 'registered',
            'registration_date' => Carbon::now(),
        ]);

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Event is full');

        $this->calendarService->registerForEvent($eventId, $userId);
    }

    public function testRegisterAfterDeadlineThrowsException()
    {
        $calendarId = $this->generateUuid();
        $eventId = $this->generateUuid();
        $userId = $this->generateUuid();

        $this->createCalendar($calendarId);

        CalendarEvent::create([
            'id' => $eventId,
            'calendar_id' => $calendarId,
            'title' => 'Test Event',
            'start_date' => Carbon::now()->addDays(10),
            'end_date' => Carbon::now()->addDays(11),
            'category' => 'event',
            'priority' => 'medium',
            'requires_registration' => true,
            'max_attendees' => 10,
            'registration_deadline' => Carbon::now()->subDay(),
        ]);

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Registration deadline has passed');

        $this->calendarService->registerForEvent($eventId, $userId);
    }

    public function testRegisterDuplicateUserThrowsException()
    {
        $calendarId = $this->generateUuid();
        $eventId = $this->generateUuid();
        $userId = $this->generateUuid();

        $this->createCalendar($calendarId);

        CalendarEvent::create([
            'id' => $eventId,
            'calendar_id' => $calendarId,
            'title' => 'Test Event',
            'start_date' => Carbon::now()->addDays(10),
            'end_date' => Carbon::now()->addDays(11),
            'category' => 'event',
            'priority' => 'medium',
            'requires_registration' => true,
            'max_attendees' => 10,
            'registration_deadline' => Carbon::now()->addDays(5),
        ]);

        CalendarEventRegistration::create([
            'id' => $this->generateUuid(),
            'event_id' => $eventId,
            'user_id' => $userId,
            'status' => 'registered',
            'registration_date' => Carbon::now(),
        ]);

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('User is already registered for this event');

        $this->calendarService->registerForEvent($eventId, $userId);
    }

    public function testBookResourceSucceeds()
    {
        $data = [
            'id' => $this->generateUuid(),
            'resource_type' => 'classroom',
            'resource_id' => 'room-101',
            'start_time' => Carbon::now()->addDays(5)->addHours(9),
            'end_time' => Carbon::now()->addDays(5)->addHours(11),
            'booked_by' => $this->generateUuid(),
            'purpose' => 'Meeting',
            'status' => 'confirmed',
        ];

        $booking = $this->calendarService->bookResource($data);

        $this->assertInstanceOf(ResourceBooking::class, $booking);
        $this->assertEquals('classroom', $booking->resource_type);
        $this->assertEquals('room-101', $booking->resource_id);
        $this->assertEquals('Meeting', $booking->purpose);
        $this->assertEquals('confirmed', $booking->status);
    }

    public function testBookResourceWithConflictThrowsException()
    {
        $startTime = Carbon::now()->addDays(5)->addHours(9);
        $endTime = Carbon::now()->addDays(5)->addHours(11);

        ResourceBooking::create([
            'id' => $this->generateUuid(),
            'resource_type' => 'classroom',
            'resource_id' => 'room-101',
            'start_time' => $startTime->copy()->subHour(),
            'end_time' => $endTime->copy()->addHour(),
            'booked_by' => $this->generateUuid(),
            'purpose' => 'Existing Booking',
            'status' => 'confirmed',
        ]);

        $newBookingData = [
            'id' => $this->generateUuid(),
            'resource_type' => 'classroom',
            'resource_id' => 'room-101',
            'start_time' => $startTime,
            'end_time' => $endTime,
            'booked_by' => $this->generateUuid(),
            'purpose' => 'New Booking',
            'status' => 'confirmed',
        ];

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Resource is already booked for selected time period');

        $this->calendarService->bookResource($newBookingData);
    }

    public function testBookResourceDifferentResourceNoConflict()
    {
        ResourceBooking::create([
            'id' => $this->generateUuid(),
            'resource_type' => 'classroom',
            'resource_id' => 'room-101',
            'start_time' => Carbon::now()->addDays(5)->addHours(9),
            'end_time' => Carbon::now()->addDays(5)->addHours(11),
            'booked_by' => $this->generateUuid(),
            'purpose' => 'Existing Booking',
            'status' => 'confirmed',
        ]);

        $newBookingData = [
            'id' => $this->generateUuid(),
            'resource_type' => 'classroom',
            'resource_id' => 'room-102',
            'start_time' => Carbon::now()->addDays(5)->addHours(10),
            'end_time' => Carbon::now()->addDays(5)->addHours(12),
            'booked_by' => $this->generateUuid(),
            'purpose' => 'New Booking',
            'status' => 'confirmed',
        ];

        $booking = $this->calendarService->bookResource($newBookingData);

        $this->assertInstanceOf(ResourceBooking::class, $booking);
        $this->assertEquals('room-102', $booking->resource_id);
    }

    public function testShareCalendarNewShare()
    {
        $calendarId = $this->generateUuid();
        $userId = $this->generateUuid();
        $this->createCalendar($calendarId);

        $result = $this->calendarService->shareCalendar(
            $calendarId,
            $userId,
            'read'
        );

        $this->assertTrue($result);

        $share = CalendarShare::where('calendar_id', $calendarId)
            ->where('user_id', $userId)
            ->first();
        $this->assertNotNull($share);
        $this->assertEquals('read', $share->permission_type);
    }

    public function testShareCalendarExistingShareUpdates()
    {
        $calendarId = $this->generateUuid();
        $userId = $this->generateUuid();
        $this->createCalendar($calendarId);

        CalendarShare::create([
            'id' => $this->generateUuid(),
            'calendar_id' => $calendarId,
            'user_id' => $userId,
            'permission_type' => 'read',
            'expires_at' => null,
        ]);

        $result = $this->calendarService->shareCalendar(
            $calendarId,
            $userId,
            'write'
        );

        $this->assertTrue($result);

        $share = CalendarShare::where('calendar_id', $calendarId)
            ->where('user_id', $userId)
            ->first();
        $this->assertEquals('write', $share->permission_type);
    }

    public function testShareNonexistentCalendarThrowsException()
    {
        $nonExistentCalendarId = '00000000-0000-0000-0000-000000000000';
        $userId = $this->generateUuid();

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Calendar not found');

        $this->calendarService->shareCalendar($nonExistentCalendarId, $userId, 'read');
    }

    public function testGetRegistrationCount()
    {
        $eventId = $this->generateUuid();
        $calendarId = $this->generateUuid();
        $this->createCalendar($calendarId);

        CalendarEventRegistration::create([
            'id' => $this->generateUuid(),
            'event_id' => $eventId,
            'user_id' => $this->generateUuid(),
            'status' => 'registered',
            'registration_date' => Carbon::now(),
        ]);

        CalendarEventRegistration::create([
            'id' => $this->generateUuid(),
            'event_id' => $eventId,
            'user_id' => $this->generateUuid(),
            'status' => 'registered',
            'registration_date' => Carbon::now(),
        ]);

        CalendarEventRegistration::create([
            'id' => $this->generateUuid(),
            'event_id' => $this->generateUuid(),
            'user_id' => $this->generateUuid(),
            'status' => 'registered',
            'registration_date' => Carbon::now(),
        ]);

        $count = $this->calendarService->getRegistrationCount($eventId);

        $this->assertEquals(2, $count);
    }

    public function testGetUpcomingEvents()
    {
        $calendarId = $this->generateUuid();
        $ownerId = $this->generateUuid();
        $userId = $this->generateUuid();

        $this->createCalendar($calendarId, $ownerId, true);

        CalendarEvent::create([
            'id' => $this->generateUuid(),
            'calendar_id' => $calendarId,
            'title' => 'Upcoming Event 1',
            'start_date' => Carbon::now()->addDays(2),
            'end_date' => Carbon::now()->addDays(3),
            'category' => 'event',
            'priority' => 'medium',
        ]);

        CalendarEvent::create([
            'id' => $this->generateUuid(),
            'calendar_id' => $calendarId,
            'title' => 'Upcoming Event 2',
            'start_date' => Carbon::now()->addDays(5),
            'end_date' => Carbon::now()->addDays(6),
            'category' => 'meeting',
            'priority' => 'high',
        ]);

        CalendarEvent::create([
            'id' => $this->generateUuid(),
            'calendar_id' => $calendarId,
            'title' => 'Past Event',
            'start_date' => Carbon::now()->subDays(5),
            'end_date' => Carbon::now()->subDays(4),
            'category' => 'event',
            'priority' => 'low',
        ]);

        $events = $this->calendarService->getUpcomingEvents($userId, 10);

        $this->assertCount(2, $events);
        $this->assertEquals('Upcoming Event 1', $events[0]['title']);
        $this->assertEquals('Upcoming Event 2', $events[1]['title']);
    }

    private function createCalendar(string $id, ?string $ownerId = null, bool $isPublic = true): void
    {
        Calendar::create([
            'id' => $id,
            'name' => 'Test Calendar',
            'description' => 'Test calendar',
            'is_public' => $isPublic,
            'owner_id' => $ownerId ?? $this->generateUuid(),
        ]);
    }

    private function generateUuid(): string
    {
        $data = random_bytes(16);
        $data[6] = chr(ord($data[6]) & 0x0F | 0x40);
        $data[8] = chr(ord($data[8]) & 0x3F | 0x80);

        return vsprintf('%s%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
    }
}
