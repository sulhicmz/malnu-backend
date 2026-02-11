<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Services\CalendarService;
use App\Models\Calendar\Calendar;
use App\Models\Calendar\CalendarEvent;
use Tests\TestCase;

/**
 * @internal
 * @covers \App\Services\CalendarService
 */
class CalendarServiceTest extends TestCase
{
    private CalendarService $calendarService;

    private $mockCalendarModel;
    private $mockCalendarEventModel;

    protected function setUp(): void
    {
        parent::setUp();

        $this->calendarService = new CalendarService();
        $this->mockCalendarModel = $this->createMock(Calendar::class);
        $this->mockCalendarEventModel = $this->createMock(CalendarEvent::class);
    }

    public function testCreateCalendar(): void
    {
        $data = [
            'name' => 'Academic Year 2026',
            'type' => 'academic',
            'owner_id' => 'user123',
            'is_public' => true,
        ];

        $this->mockCalendarModel->expects($this->once())
            ->method('create')
            ->with($data)
            ->willReturn(new Calendar($data));

        $result = $this->calendarService->createCalendar($data);

        $this->assertInstanceOf(Calendar::class, $result);
    }

    public function testGetCalendar(): void
    {
        $calendarId = 'cal123';
        $expectedCalendar = new Calendar(['id' => $calendarId, 'name' => 'Test Calendar']);

        $this->mockCalendarModel->expects($this->once())
            ->method('find')
            ->with($calendarId)
            ->willReturn($expectedCalendar);

        $result = $this->calendarService->getCalendar($calendarId);

        $this->assertInstanceOf(Calendar::class, $result);
        $this->assertEquals('Test Calendar', $result->name);
    }

    public function testGetCalendarWithNonExistentId(): void
    {
        $nonExistentId = 'cal999';

        $this->mockCalendarModel->expects($this->once())
            ->method('find')
            ->with($nonExistentId)
            ->willReturn(null);

        $result = $this->calendarService->getCalendar($nonExistentId);

        $this->assertNull($result);
    }

    public function testUpdateCalendar(): void
    {
        $calendarId = 'cal123';
        $updateData = [
            'name' => 'Updated Calendar',
            'is_public' => false,
        ];

        $existingCalendar = new Calendar(['id' => $calendarId, 'name' => 'Old Calendar']);

        $this->mockCalendarModel->expects($this->once())
            ->method('find')
            ->with($calendarId)
            ->willReturn($existingCalendar);

        $this->mockCalendarModel->expects($this->once())
            ->method('update')
            ->with($updateData)
            ->willReturn(true);

        $result = $this->calendarService->updateCalendar($calendarId, $updateData);

        $this->assertTrue($result);
    }

    public function testUpdateCalendarWithNonExistentId(): void
    {
        $nonExistentId = 'cal999';
        $updateData = ['name' => 'Updated'];

        $this->mockCalendarModel->expects($this->once())
            ->method('find')
            ->with($nonExistentId)
            ->willReturn(null);

        $result = $this->calendarService->updateCalendar($nonExistentId, $updateData);

        $this->assertFalse($result);
    }

    public function testDeleteCalendar(): void
    {
        $calendarId = 'cal123';

        $existingCalendar = new Calendar(['id' => $calendarId, 'name' => 'Test Calendar']);

        $this->mockCalendarModel->expects($this->once())
            ->method('find')
            ->with($calendarId)
            ->willReturn($existingCalendar);

        $this->mockCalendarModel->expects($this->once())
            ->method('delete')
            ->willReturn(true);

        $result = $this->calendarService->deleteCalendar($calendarId);

        $this->assertTrue($result);
    }

    public function testDeleteCalendarWithNonExistentId(): void
    {
        $nonExistentId = 'cal999';

        $this->mockCalendarModel->expects($this->once())
            ->method('find')
            ->with($nonExistentId)
            ->willReturn(null);

        $result = $this->calendarService->deleteCalendar($nonExistentId);

        $this->assertFalse($result);
    }

    public function testCreateEvent(): void
    {
        $data = [
            'calendar_id' => 'cal123',
            'title' => 'Staff Meeting',
            'start_date' => '2026-02-10 10:00:00',
            'end_date' => '2026-02-10 11:00:00',
            'location' => 'Conference Room A',
            'type' => 'meeting',
        ];

        $this->mockCalendarEventModel->expects($this->once())
            ->method('create')
            ->with($data)
            ->willReturn(new CalendarEvent($data));

        $result = $this->calendarService->createEvent($data);

        $this->assertInstanceOf(CalendarEvent::class, $result);
    }

    public function testGetEvent(): void
    {
        $eventId = 'evt456';
        $expectedEvent = new CalendarEvent(['id' => $eventId, 'title' => 'Test Event']);

        $this->mockCalendarEventModel->expects($this->once())
            ->method('find')
            ->with($eventId)
            ->willReturn($expectedEvent);

        $result = $this->calendarService->getEvent($eventId);

        $this->assertInstanceOf(CalendarEvent::class, $result);
        $this->assertEquals('Test Event', $result->title);
    }

    public function testGetEventWithNonExistentId(): void
    {
        $nonExistentId = 'evt999';

        $this->mockCalendarEventModel->expects($this->once())
            ->method('find')
            ->with($nonExistentId)
            ->willReturn(null);

        $result = $this->calendarService->getEvent($nonExistentId);

        $this->assertNull($result);
    }

    public function testUpdateEvent(): void
    {
        $eventId = 'evt456';
        $updateData = [
            'title' => 'Updated Event',
            'location' => 'Conference Room B',
        ];

        $existingEvent = new CalendarEvent(['id' => $eventId, 'title' => 'Old Event']);

        $this->mockCalendarEventModel->expects($this->once())
            ->method('find')
            ->with($eventId)
            ->willReturn($existingEvent);

        $this->mockCalendarEventModel->expects($this->once())
            ->method('update')
            ->with($updateData)
            ->willReturn(true);

        $result = $this->calendarService->updateEvent($eventId, $updateData);

        $this->assertTrue($result);
    }

    public function testUpdateEventWithNonExistentId(): void
    {
        $nonExistentId = 'evt999';
        $updateData = ['title' => 'Updated'];

        $this->mockCalendarEventModel->expects($this->once())
            ->method('find')
            ->with($nonExistentId)
            ->willReturn(null);

        $result = $this->calendarService->updateEvent($nonExistentId, $updateData);

        $this->assertFalse($result);
    }

    public function testDeleteEvent(): void
    {
        $eventId = 'evt456';

        $existingEvent = new CalendarEvent(['id' => $eventId, 'title' => 'Test Event']);

        $this->mockCalendarEventModel->expects($this->once())
            ->method('find')
            ->with($eventId)
            ->willReturn($existingEvent);

        $this->mockCalendarEventModel->expects($this->once())
            ->method('delete')
            ->willReturn(true);

        $result = $this->calendarService->deleteEvent($eventId);

        $this->assertTrue($result);
    }

    public function testDeleteEventWithNonExistentId(): void
    {
        $nonExistentId = 'evt999';

        $this->mockCalendarEventModel->expects($this->once())
            ->method('find')
            ->with($nonExistentId)
            ->willReturn(null);

        $result = $this->calendarService->deleteEvent($nonExistentId);

        $this->assertFalse($result);
    }
}
