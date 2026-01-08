<?php

declare(strict_types=1);

namespace Tests\Feature\Alumni;

use App\Models\Alumni\Alumni;
use App\Models\Alumni\AlumniCareer;
use App\Models\Alumni\AlumniDonation;
use App\Models\Alumni\AlumniEvent;
use App\Models\Alumni\AlumniEventRegistration;
use App\Models\Alumni\AlumniEngagement;
use App\Models\Alumni\AlumniMentorship;
use App\Models\SchoolManagement\Student;
use App\Models\User;
use Tests\TestCase;

class AlumniManagementTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
    }

    public function test_can_create_alumni_profile()
    {
        $student = Student::factory()->create();
        $user = User::factory()->create();

        $data = [
            'student_id' => $student->id,
            'user_id' => $user->id,
            'graduation_year' => 2020,
            'graduation_class' => 'Class of 2020',
            'degree' => 'Bachelor of Science',
            'field_of_study' => 'Computer Science',
            'current_company' => 'Tech Company',
            'current_position' => 'Software Engineer',
            'industry' => 'Technology',
            'is_public' => true,
            'is_verified' => true,
        ];

        $alumni = Alumni::create($data);

        $this->assertDatabaseHas('alumni', [
            'student_id' => $student->id,
            'user_id' => $user->id,
            'graduation_year' => 2020,
        ]);
    }

    public function test_can_filter_alumni_by_industry()
    {
        $industry = 'Technology';

        Alumni::factory()->create(['industry' => 'Technology', 'is_public' => true, 'is_verified' => true]);
        Alumni::factory()->create(['industry' => 'Finance', 'is_public' => true, 'is_verified' => true]);

        $results = Alumni::public()->verified()->byIndustry($industry)->get();

        $this->assertCount(1, $results);
        $this->assertEquals('Technology', $results->first()->industry);
    }

    public function test_can_filter_alumni_by_graduation_year()
    {
        $year = 2020;

        Alumni::factory()->create(['graduation_year' => 2020, 'is_public' => true]);
        Alumni::factory()->create(['graduation_year' => 2019, 'is_public' => true]);

        $results = Alumni::public()->byGraduationYear($year)->get();

        $this->assertCount(1, $results);
        $this->assertEquals(2020, $results->first()->graduation_year);
    }

    public function test_can_create_alumni_career()
    {
        $alumni = Alumni::factory()->create();

        $data = [
            'alumni_id' => $alumni->id,
            'company_name' => 'New Company',
            'position' => 'Senior Developer',
            'industry' => 'Technology',
            'start_date' => '2023-01-01',
            'is_current' => true,
        ];

        $career = AlumniCareer::create($data);

        $this->assertDatabaseHas('alumni_careers', [
            'alumni_id' => $alumni->id,
            'company_name' => 'New Company',
        ]);
    }

    public function test_can_get_current_careers()
    {
        $alumni = Alumni::factory()->create();

        AlumniCareer::factory()->create([
            'alumni_id' => $alumni->id,
            'is_current' => true,
        ]);

        AlumniCareer::factory()->create([
            'alumni_id' => $alumni->id,
            'is_current' => false,
        ]);

        $currentCareers = AlumniCareer::where('alumni_id', $alumni->id)->current()->get();

        $this->assertCount(1, $currentCareers);
        $this->assertTrue($currentCareers->first()->is_current);
    }

    public function test_can_create_alumni_donation()
    {
        $alumni = Alumni::factory()->create();

        $data = [
            'alumni_id' => $alumni->id,
            'amount' => 1000.00,
            'currency' => 'USD',
            'donation_type' => 'one-time',
            'campaign' => 'Annual Fund',
            'donation_date' => '2023-01-01',
            'status' => 'completed',
        ];

        $donation = AlumniDonation::create($data);

        $this->assertDatabaseHas('alumni_donations', [
            'alumni_id' => $alumni->id,
            'amount' => 1000.00,
        ]);
    }

    public function test_can_filter_donations_by_campaign()
    {
        $campaign = 'Annual Fund';

        $donation1 = AlumniDonation::factory()->create(['campaign' => $campaign, 'status' => 'completed']);
        AlumniDonation::factory()->create(['campaign' => 'Scholarship Fund', 'status' => 'completed']);

        $results = AlumniDonation::completed()->byCampaign($campaign)->get();

        $this->assertCount(1, $results);
        $this->assertEquals($campaign, $results->first()->campaign);
    }

    public function test_anonymous_donation_returns_anonymous_name()
    {
        $donation = AlumniDonation::factory()->create([
            'is_anonymous' => true,
            'donor_name' => 'John Doe',
        ]);

        $this->assertEquals('Anonymous', $donation->donor_name);
    }

    public function test_can_create_alumni_event()
    {
        $data = [
            'name' => 'Alumni Reunion 2023',
            'event_type' => 'reunion',
            'event_date' => '2023-12-15 18:00:00',
            'location' => 'School Auditorium',
            'max_capacity' => 200,
            'status' => 'upcoming',
        ];

        $event = AlumniEvent::create($data);

        $this->assertDatabaseHas('alumni_events', [
            'name' => 'Alumni Reunion 2023',
            'event_type' => 'reunion',
        ]);
    }

    public function test_can_get_upcoming_events()
    {
        AlumniEvent::factory()->create([
            'event_date' => now()->addDays(7),
            'status' => 'upcoming',
        ]);

        AlumniEvent::factory()->create([
            'event_date' => now()->subDays(7),
            'status' => 'completed',
        ]);

        $upcomingEvents = AlumniEvent::upcoming()->get();

        $this->assertCount(1, $upcomingEvents);
        $this->assertEquals('upcoming', $upcomingEvents->first()->status);
    }

    public function test_event_capacity_status()
    {
        $event = AlumniEvent::factory()->create([
            'max_capacity' => 100,
            'current_attendees' => 80,
        ]);

        $this->assertEquals('available', $event->capacity_status);
    }

    public function test_event_is_fully_booked()
    {
        $event = AlumniEvent::factory()->create([
            'max_capacity' => 100,
            'current_attendees' => 100,
        ]);

        $this->assertTrue($event->isFullyBooked());
    }

    public function test_can_register_for_event()
    {
        $event = AlumniEvent::factory()->create([
            'max_capacity' => 100,
            'current_attendees' => 50,
        ]);

        $alumni = Alumni::factory()->create();

        $registration = AlumniEventRegistration::create([
            'event_id' => $event->id,
            'alumni_id' => $alumni->id,
            'name' => $alumni->full_name,
            'email' => $alumni->email,
            'guests' => 0,
            'registration_date' => now(),
        ]);

        $this->assertDatabaseHas('alumni_event_registrations', [
            'event_id' => $event->id,
            'alumni_id' => $alumni->id,
        ]);
    }

    public function test_registration_total_attendees()
    {
        $registration = AlumniEventRegistration::factory()->create([
            'is_attending' => true,
            'guests' => 2,
        ]);

        $this->assertEquals(3, $registration->total_attendees);
    }

    public function test_can_check_in_attendee()
    {
        $registration = AlumniEventRegistration::factory()->create([
            'check_in_status' => false,
        ]);

        $registration->markAsCheckedIn();

        $this->assertTrue($registration->check_in_status);
        $this->assertNotNull($registration->check_in_time);
    }

    public function test_can_create_mentorship()
    {
        $mentor = Alumni::factory()->create(['mentor_availability' => true]);
        $student = Student::factory()->create();

        $data = [
            'mentor_id' => $mentor->id,
            'student_id' => $student->id,
            'status' => 'pending',
            'focus_area' => 'Career Guidance',
            'goals' => 'Learn about tech industry',
        ];

        $mentorship = AlumniMentorship::create($data);

        $this->assertDatabaseHas('alumni_mentorships', [
            'mentor_id' => $mentor->id,
            'student_id' => $student->id,
            'status' => 'pending',
        ]);
    }

    public function test_can_activate_mentorship()
    {
        $mentorship = AlumniMentorship::factory()->create(['status' => 'pending']);

        $mentorship->activate();

        $this->assertEquals('active', $mentorship->status);
        $this->assertNotNull($mentorship->start_date);
    }

    public function test_can_complete_mentorship()
    {
        $mentorship = AlumniMentorship::factory()->create([
            'status' => 'active',
            'start_date' => now()->subMonths(3),
        ]);

        $mentorship->complete();

        $this->assertEquals('completed', $mentorship->status);
        $this->assertNotNull($mentorship->end_date);
    }

    public function test_can_increment_mentorship_sessions()
    {
        $mentorship = AlumniMentorship::factory()->create(['sessions_count' => 0]);

        $mentorship->incrementSessions();

        $this->assertEquals(1, $mentorship->sessions_count);
    }

    public function test_can_create_engagement()
    {
        $alumni = Alumni::factory()->create();

        $data = [
            'alumni_id' => $alumni->id,
            'engagement_type' => 'volunteering',
            'description' => 'Volunteered at career fair',
            'engagement_date' => now(),
            'category' => 'community',
        ];

        $engagement = AlumniEngagement::create($data);

        $this->assertDatabaseHas('alumni_engagements', [
            'alumni_id' => $alumni->id,
            'engagement_type' => 'volunteering',
        ]);
    }

    public function test_can_filter_engagements_by_type()
    {
        $type = 'volunteering';

        $engagement1 = AlumniEngagement::factory()->create(['engagement_type' => $type]);
        AlumniEngagement::factory()->create(['engagement_type' => 'donation']);

        $results = AlumniEngagement::byType($type)->get();

        $this->assertCount(1, $results);
        $this->assertEquals($type, $results->first()->engagement_type);
    }

    public function test_can_filter_engagements_by_year()
    {
        $year = now()->year;

        AlumniEngagement::factory()->create(['engagement_date' => now()]);
        AlumniEngagement::factory()->create(['engagement_date' => now()->subYear()]);

        $results = AlumniEngagement::byYear($year)->get();

        $this->assertCount(1, $results);
        $this->assertEquals($year, $results->first()->engagement_date->year);
    }

    public function test_alumni_full_name_attribute()
    {
        $user = User::factory()->create(['name' => 'John Doe']);
        $alumni = Alumni::factory()->create(['user_id' => $user->id]);

        $this->assertEquals('John Doe', $alumni->full_name);
    }

    public function test_alumni_email_attribute()
    {
        $user = User::factory()->create(['email' => 'john@example.com']);
        $alumni = Alumni::factory()->create(['user_id' => $user->id]);

        $this->assertEquals('john@example.com', $alumni->email);
    }

    public function test_mentor_availability_scope()
    {
        Alumni::factory()->create(['mentor_availability' => false, 'is_public' => true, 'is_verified' => true]);
        Alumni::factory()->create(['mentor_availability' => true, 'is_public' => true, 'is_verified' => true]);

        $availableMentors = Alumni::public()->verified()->availableForMentorship()->get();

        $this->assertCount(1, $availableMentors);
        $this->assertTrue($availableMentors->first()->mentor_availability);
    }

    public function test_event_has_capacity()
    {
        $event1 = AlumniEvent::factory()->create(['max_capacity' => 100, 'current_attendees' => 50]);
        $event2 = AlumniEvent::factory()->create(['max_capacity' => 100, 'current_attendees' => 100]);
        $event3 = AlumniEvent::factory()->create(['max_capacity' => null, 'current_attendees' => 50]);

        $this->assertTrue($event1->hasCapacity());
        $this->assertFalse($event2->hasCapacity());
        $this->assertTrue($event3->hasCapacity());
    }

    public function test_event_available_slots()
    {
        $event1 = AlumniEvent::factory()->create(['max_capacity' => 100, 'current_attendees' => 50]);
        $event2 = AlumniEvent::factory()->create(['max_capacity' => null, 'current_attendees' => 50]);

        $this->assertEquals(50, $event1->available_slots);
        $this->assertNull($event2->available_slots);
    }
}
