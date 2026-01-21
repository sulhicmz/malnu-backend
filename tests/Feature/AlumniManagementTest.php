<?php

declare(strict_types=1);

namespace Tests\Feature;

use Tests\TestCase;
use App\Services\AlumniManagementService;
use App\Models\AlumniManagement\AlumniProfile;
use App\Models\AlumniManagement\AlumniCareer;
use App\Models\AlumniManagement\AlumniAchievement;
use App\Models\AlumniManagement\AlumniMentorship;
use App\Models\AlumniManagement\AlumniDonation;
use App\Models\AlumniManagement\AlumniEvent;
use App\Models\AlumniManagement\AlumniEventRegistration;
use App\Models\SchoolManagement\Student;
use App\Models\User;
use Carbon\Carbon;

class AlumniManagementTest extends TestCase
{
    private AlumniManagementService $alumniService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->alumniService = $this->app->get(AlumniManagementService::class);
    }

    public function test_create_alumni_profile(): void
    {
        $student = Student::first();
        $user = User::first();

        if (!$student || !$user) {
            $this->markTestSkipped('No student or user data available');
            return;
        }

        $data = [
            'student_id' => $student->id,
            'user_id' => $user->id,
            'graduation_year' => '2020',
            'degree' => 'Bachelor of Science',
            'field_of_study' => 'Computer Science',
            'bio' => 'Software engineer at Tech Corp',
            'public_profile' => true,
            'allow_contact' => true,
            'privacy_consent' => true,
        ];

        $profile = $this->alumniService->createProfile($data);

        $this->assertDatabaseHas('alumni_profiles', [
            'student_id' => $student->id,
            'graduation_year' => '2020',
            'degree' => 'Bachelor of Science',
        ]);
    }

    public function test_get_alumni_profile(): void
    {
        $profile = AlumniProfile::first();

        if (!$profile) {
            $this->markTestSkipped('No alumni profile available');
            return;
        }

        $retrievedProfile = $this->alumniService->getProfile($profile->id);

        $this->assertNotNull($retrievedProfile);
        $this->assertEquals($profile->id, $retrievedProfile['id']);
    }

    public function test_update_alumni_profile(): void
    {
        $profile = AlumniProfile::first();

        if (!$profile) {
            $this->markTestSkipped('No alumni profile available');
            return;
        }

        $updated = $this->alumniService->updateProfile($profile->id, [
            'bio' => 'Updated bio information',
        ]);

        $this->assertTrue($updated);
    }

    public function test_delete_alumni_profile(): void
    {
        $profile = AlumniProfile::first();

        if (!$profile) {
            $this->markTestSkipped('No alumni profile available');
            return;
        }

        $deleted = $this->alumniService->deleteProfile($profile->id);

        $this->assertTrue($deleted);
        $this->assertDatabaseMissing('alumni_profiles', ['id' => $profile->id]);
    }

    public function test_add_career(): void
    {
        $alumni = AlumniProfile::first();

        if (!$alumni) {
            $this->markTestSkipped('No alumni profile available');
            return;
        }

        $data = [
            'company' => 'Tech Corp',
            'position' => 'Senior Software Engineer',
            'industry' => 'Technology',
            'current_job' => true,
        ];

        $career = $this->alumniService->addCareer($alumni->id, $data);

        $this->assertDatabaseHas('alumni_careers', [
            'alumni_id' => $alumni->id,
            'company' => 'Tech Corp',
        ]);
    }

    public function test_update_career(): void
    {
        $career = AlumniCareer::first();

        if (!$career) {
            $this->markTestSkipped('No career available');
            return;
        }

        $updated = $this->alumniService->updateCareer($career->id, [
            'position' => 'Updated Position',
        ]);

        $this->assertTrue($updated);
    }

    public function test_delete_career(): void
    {
        $career = AlumniCareer::first();

        if (!$career) {
            $this->markTestSkipped('No career available');
            return;
        }

        $deleted = $this->alumniService->deleteCareer($career->id);

        $this->assertTrue($deleted);
        $this->assertDatabaseMissing('alumni_careers', ['id' => $career->id]);
    }

    public function test_add_achievement(): void
    {
        $alumni = AlumniProfile::first();

        if (!$alumni) {
            $this->markTestSkipped('No alumni profile available');
            return;
        }

        $data = [
            'achievement_type' => 'award',
            'title' => 'Employee of the Year',
            'description' => 'Outstanding performance in 2023',
            'achievement_date' => '2023-12-31',
        ];

        $achievement = $this->alumniService->addAchievement($alumni->id, $data);

        $this->assertDatabaseHas('alumni_achievements', [
            'alumni_id' => $alumni->id,
            'title' => 'Employee of the Year',
        ]);
    }

    public function test_create_mentorship(): void
    {
        $alumni = AlumniProfile::first();
        $student = Student::first();

        if (!$alumni || !$student) {
            $this->markTestSkipped('No alumni profile or student available');
            return;
        }

        $data = [
            'alumni_id' => $alumni->id,
            'student_id' => $student->id,
            'status' => 'pending',
            'focus_area' => 'Career guidance',
        ];

        $mentorship = $this->alumniService->createMentorship($data);

        $this->assertDatabaseHas('alumni_mentorships', [
            'alumni_id' => $alumni->id,
            'student_id' => $student->id,
            'status' => 'pending',
        ]);
    }

    public function test_update_mentorship_status(): void
    {
        $mentorship = AlumniMentorship::first();

        if (!$mentorship) {
            $this->markTestSkipped('No mentorship available');
            return;
        }

        $updated = $this->alumniService->updateMentorship($mentorship->id, [
            'status' => 'active',
        ]);

        $this->assertTrue($updated);
    }

    public function test_record_donation(): void
    {
        $alumni = AlumniProfile::first();

        if (!$alumni) {
            $this->markTestSkipped('No alumni profile available');
            return;
        }

        $data = [
            'amount' => 500.00,
            'currency' => 'USD',
            'donation_type' => 'general',
            'anonymous' => false,
            'acknowledged' => false,
        ];

        $donation = $this->alumniService->recordDonation($alumni->id, $data);

        $this->assertDatabaseHas('alumni_donations', [
            'alumni_id' => $alumni->id,
            'amount' => 500.00,
        ]);
    }

    public function test_get_donations(): void
    {
        $alumni = AlumniProfile::first();

        if (!$alumni) {
            $this->markTestSkipped('No alumni profile available');
            return;
        }

        $donations = $this->alumniService->getDonations($alumni->id);

        $this->assertIsArray($donations);
    }

    public function test_create_alumni_event(): void
    {
        $user = User::first();

        if (!$user) {
            $this->markTestSkipped('No user available');
            return;
        }

        $data = [
            'created_by' => $user->id,
            'title' => 'Annual Alumni Reunion 2024',
            'description' => 'Join us for the annual alumni reunion',
            'event_type' => 'reunion',
            'location' => 'School Auditorium',
            'event_date' => Carbon::now()->addDays(30)->format('Y-m-d H:i:s'),
            'max_attendees' => 100,
        ];

        $event = $this->alumniService->createEvent($data);

        $this->assertDatabaseHas('alumni_events', [
            'title' => 'Annual Alumni Reunion 2024',
        ]);
    }

    public function test_get_upcoming_events(): void
    {
        $events = $this->alumniService->getUpcomingEvents();

        $this->assertIsArray($events);
    }

    public function test_register_for_event(): void
    {
        $event = AlumniEvent::first();
        $alumni = AlumniProfile::first();

        if (!$event || !$alumni) {
            $this->markTestSkipped('No event or alumni profile available');
            return;
        }

        $data = [
            'notes' => 'Looking forward to attending',
        ];

        $registration = $this->alumniService->registerForEvent($event->id, $alumni->id, $data);

        $this->assertDatabaseHas('alumni_event_registrations', [
            'event_id' => $event->id,
            'alumni_id' => $alumni->id,
        ]);
    }

    public function test_cancel_registration(): void
    {
        $registration = AlumniEventRegistration::first();

        if (!$registration) {
            $this->markTestSkipped('No event registration available');
            return;
        }

        $updated = $this->alumniService->cancelRegistration($registration->id);

        $this->assertTrue($updated);

        $cancelledRegistration = AlumniEventRegistration::find($registration->id);
        $this->assertEquals('cancelled', $cancelledRegistration->attendance_status);
    }

    public function test_get_alumni_statistics(): void
    {
        $statistics = $this->alumniService->getAlumniStatistics();

        $this->assertIsArray($statistics);
        $this->assertArrayHasKey('total_alumni', $statistics);
        $this->assertArrayHasKey('public_profiles', $statistics);
        $this->assertArrayHasKey('active_mentorships', $statistics);
        $this->assertArrayHasKey('total_donations', $statistics);
        $this->assertArrayHasKey('upcoming_events', $statistics);
    }
}
