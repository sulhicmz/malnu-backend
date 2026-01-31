<?php

declare(strict_types=1);

namespace Tests\Feature;

use Tests\TestCase;
use App\Services\CalendarService;
use App\Models\Calendar\Calendar;
use App\Models\Calendar\CalendarEvent;
use App\Models\Calendar\AcademicTerm;
use App\Models\Calendar\Holiday;
use App\Models\Calendar\EventAttendance;
use App\Models\User;

class CalendarTest extends TestCase
{
    private CalendarService $calendarService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->calendarService = $this->app->get(CalendarService::class);
    }

    public function test_create_academic_term(): void
    {
        $data = [
            'name' => 'Term 1 2026',
            'academic_year' => '2026',
            'term_number' => 1,
            'start_date' => '2026-01-01',
            'end_date' => '2026-06-30',
            'is_current' => true,
        ];

        $term = $this->calendarService->createAcademicTerm($data);

        $this->assertDatabaseHas('academic_terms', [
            'name' => 'Term 1 2026',
            'academic_year' => '2026',
            'term_number' => 1,
        ]);
    }

    public function test_get_current_academic_term(): void
    {
        $data = [
            'name' => 'Current Term',
            'academic_year' => '2026',
            'term_number' => 1,
            'start_date' => date('Y-m-d'),
            'end_date' => date('Y-m-d', strtotime('+6 months')),
            'is_current' => true,
        ];

        $this->calendarService->createAcademicTerm($data);
        $term = $this->calendarService->getCurrentAcademicTerm();

        $this->assertNotNull($term);
        $this->assertEquals('Current Term', $term->name);
        $this->assertTrue($term->is_current);
    }

    public function test_update_academic_term_sets_current_flag(): void
    {
        $term1 = $this->calendarService->createAcademicTerm([
            'name' => 'Term 1',
            'academic_year' => '2026',
            'term_number' => 1,
            'start_date' => '2026-01-01',
            'end_date' => '2026-06-30',
            'is_current' => true,
        ]);

        $term2 = $this->calendarService->createAcademicTerm([
            'name' => 'Term 2',
            'academic_year' => '2026',
            'term_number' => 2,
            'start_date' => '2026-07-01',
            'end_date' => '2026-12-31',
            'is_current' => false,
        ]);

        $this->calendarService->updateAcademicTerm($term2->id, ['is_current' => true]);

        $updatedTerm1 = $this->calendarService->getAcademicTerm($term1->id);
        $updatedTerm2 = $this->calendarService->getAcademicTerm($term2->id);

        $this->assertFalse($updatedTerm1->is_current);
        $this->assertTrue($updatedTerm2->is_current);
    }

    public function test_create_holiday(): void
    {
        $data = [
            'name' => 'New Year Holiday',
            'start_date' => '2026-01-01',
            'end_date' => '2026-01-01',
            'type' => 'public',
            'is_school_wide' => true,
        ];

        $holiday = $this->calendarService->createHoliday($data);

        $this->assertDatabaseHas('holidays', [
            'name' => 'New Year Holiday',
            'type' => 'public',
            'is_school_wide' => true,
        ]);
    }

    public function test_get_holidays_by_date_range(): void
    {
        $this->calendarService->createHoliday([
            'name' => 'Holiday 1',
            'start_date' => '2026-01-15',
            'end_date' => '2026-01-15',
            'type' => 'public',
            'is_school_wide' => true,
        ]);

        $this->calendarService->createHoliday([
            'name' => 'Holiday 2',
            'start_date' => '2026-02-01',
            'end_date' => '2026-02-01',
            'type' => 'public',
            'is_school_wide' => true,
        ]);

        $holidays = $this->calendarService->getHolidaysByDateRange(
            new \DateTime('2026-01-01'),
            new \DateTime('2026-02-28')
        );

        $this->assertCount(2, $holidays);
    }

    public function test_get_upcoming_holidays(): void
    {
        $this->calendarService->createHoliday([
            'name' => 'Upcoming Holiday',
            'start_date' => date('Y-m-d', strtotime('+30 days')),
            'end_date' => date('Y-m-d', strtotime('+30 days')),
            'type' => 'public',
            'is_school_wide' => true,
        ]);

        $holidays = $this->calendarService->getUpcomingHolidays(90);

        $this->assertGreaterThanOrEqual(1, count($holidays));
    }

    public function test_check_in_to_event(): void
    {
        $user = User::first();
        $calendar = $this->calendarService->createCalendar([
            'name' => 'Test Calendar',
            'type' => 'general',
            'is_public' => true,
        ]);

        $event = $this->calendarService->createEvent([
            'calendar_id' => $calendar->id,
            'title' => 'Test Event',
            'start_date' => date('Y-m-d H:i:s'),
            'end_date' => date('Y-m-d H:i:s', strtotime('+2 hours')),
            'category' => 'event',
        ]);

        if (!$user) {
            $this->markTestSkipped('No user data available');
            return;
        }

        $attendance = $this->calendarService->checkInEvent($event->id, $user->id);

        $this->assertDatabaseHas('event_attendance', [
            'event_id' => $event->id,
            'user_id' => $user->id,
            'status' => 'present',
        ]);
    }

    public function test_check_out_from_event(): void
    {
        $user = User::first();
        $calendar = $this->calendarService->createCalendar([
            'name' => 'Test Calendar',
            'type' => 'general',
            'is_public' => true,
        ]);

        $event = $this->calendarService->createEvent([
            'calendar_id' => $calendar->id,
            'title' => 'Test Event',
            'start_date' => date('Y-m-d H:i:s'),
            'end_date' => date('Y-m-d H:i:s', strtotime('+2 hours')),
            'category' => 'event',
        ]);

        if (!$user) {
            $this->markTestSkipped('No user data available');
            return;
        }

        $this->calendarService->checkInEvent($event->id, $user->id);
        $this->calendarService->checkOutEvent($event->id, $user->id);

        $attendance = EventAttendance::where('event_id', $event->id)
            ->where('user_id', $user->id)
            ->first();

        $this->assertNotNull($attendance->check_out_time);
    }

    public function test_mark_event_attendance(): void
    {
        $user = User::first();
        $calendar = $this->calendarService->createCalendar([
            'name' => 'Test Calendar',
            'type' => 'general',
            'is_public' => true,
        ]);

        $event = $this->calendarService->createEvent([
            'calendar_id' => $calendar->id,
            'title' => 'Test Event',
            'start_date' => date('Y-m-d H:i:s'),
            'end_date' => date('Y-m-d H:i:s', strtotime('+2 hours')),
            'category' => 'event',
        ]);

        if (!$user) {
            $this->markTestSkipped('No user data available');
            return;
        }

        $attendance = $this->calendarService->markEventAttendance($event->id, $user->id, 'present', ['notes' => 'Good participation']);

        $this->assertDatabaseHas('event_attendance', [
            'event_id' => $event->id,
            'user_id' => $user->id,
            'status' => 'present',
        ]);
    }

    public function test_get_event_attendance_stats(): void
    {
        $users = User::limit(3)->get();

        if (count($users) < 3) {
            $this->markTestSkipped('Need at least 3 users for test');
            return;
        }

        $calendar = $this->calendarService->createCalendar([
            'name' => 'Test Calendar',
            'type' => 'general',
            'is_public' => true,
        ]);

        $event = $this->calendarService->createEvent([
            'calendar_id' => $calendar->id,
            'title' => 'Test Event',
            'start_date' => date('Y-m-d H:i:s'),
            'end_date' => date('Y-m-d H:i:s', strtotime('+2 hours')),
            'category' => 'event',
        ]);

        $this->calendarService->markEventAttendance($event->id, $users[0]->id, 'present');
        $this->calendarService->markEventAttendance($event->id, $users[1]->id, 'absent');
        $this->calendarService->markEventAttendance($event->id, $users[2]->id, 'present');

        $stats = $this->calendarService->getEventAttendanceStats($event->id);

        $this->assertEquals(3, $stats['total']);
        $this->assertEquals(2, $stats['present']);
        $this->assertEquals(1, $stats['absent']);
        $this->assertEquals(0, $stats['late']);
    }

    public function test_delete_academic_term(): void
    {
        $term = $this->calendarService->createAcademicTerm([
            'name' => 'Term to Delete',
            'academic_year' => '2026',
            'term_number' => 1,
            'start_date' => '2026-01-01',
            'end_date' => '2026-06-30',
            'is_current' => false,
        ]);

        $result = $this->calendarService->deleteAcademicTerm($term->id);

        $this->assertTrue($result);
        $this->assertDatabaseMissing('academic_terms', ['id' => $term->id]);
    }

    public function test_delete_holiday(): void
    {
        $holiday = $this->calendarService->createHoliday([
            'name' => 'Holiday to Delete',
            'start_date' => '2026-01-01',
            'end_date' => '2026-01-01',
            'type' => 'public',
            'is_school_wide' => true,
        ]);

        $result = $this->calendarService->deleteHoliday($holiday->id);

        $this->assertTrue($result);
        $this->assertDatabaseMissing('holidays', ['id' => $holiday->id]);
    }
}
