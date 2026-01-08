<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\ParentPortal\ParentStudentRelationship;
use App\Services\ParentPortalService;
use App\Services\ParentCommunicationService;
use App\Services\ParentEngagementService;
use Tests\TestCase;
use App\Models\User;
use App\Models\SchoolManagement\Student;

class ParentPortalTest extends TestCase
{
    protected string $parentId;
    protected string $studentId;
    protected string $teacherId;

    protected function setUp(): void
    {
        parent::setUp();

        $this->parentId = User::factory()->create(['role' => 'parent'])->id;
        $this->studentId = Student::factory()->create()->id;
        $this->teacherId = User::factory()->create(['role' => 'teacher'])->id;

        ParentStudentRelationship::create([
            'id' => \Ramsey\Uuid\Uuid::uuid4()->toString(),
            'parent_id' => $this->parentId,
            'student_id' => $this->studentId,
            'relationship_type' => 'father',
            'is_primary_contact' => true,
            'has_custody' => true,
        ]);
    }

    public function test_parent_can_get_their_children()
    {
        $service = new ParentPortalService($this->app->get(ParentPortalService::class));
        $children = $service->getParentChildren($this->parentId);

        $this->assertIsArray($children);
        $this->assertCount(1, $children);
        $this->assertEquals($this->studentId, $children[0]['id']);
    }

    public function test_parent_cannot_access_other_children()
    {
        $this->expectException(\RuntimeException::class);

        $otherStudentId = Student::factory()->create()->id;
        $service = new ParentPortalService($this->app->get(ParentPortalService::class));
        $service->getStudentDashboard($this->parentId, $otherStudentId);
    }

    public function test_parent_can_send_message()
    {
        $service = new ParentCommunicationService();
        $message = $service->sendMessage(
            $this->parentId,
            $this->teacherId,
            'Test Subject',
            'Test message content'
        );

        $this->assertEquals($this->parentId, $message->sender_id);
        $this->assertEquals($this->teacherId, $message->recipient_id);
        $this->assertEquals('Test Subject', $message->subject);
        $this->assertFalse($message->is_read);
    }

    public function test_parent_can_mark_message_as_read()
    {
        $commService = new ParentCommunicationService();
        $message = $commService->sendMessage(
            $this->parentId,
            $this->teacherId,
            'Test Subject',
            'Test message'
        );

        $result = $commService->markMessageAsRead($this->teacherId, $message->id);

        $this->assertTrue($result);

        $message->refresh();
        $this->assertTrue($message->is_read);
    }

    public function test_parent_can_schedule_conference()
    {
        $commService = new ParentCommunicationService();
        $conference = $commService->scheduleConference(
            $this->parentId,
            $this->teacherId,
            $this->studentId,
            date('Y-m-d H:i:s', strtotime('+1 week'))
        );

        $this->assertEquals($this->parentId, $conference->parent_id);
        $this->assertEquals($this->teacherId, $conference->teacher_id);
        $this->assertEquals($this->studentId, $conference->student_id);
        $this->assertEquals('scheduled', $conference->status);
    }

    public function test_parent_can_update_conference_status()
    {
        $commService = new ParentCommunicationService();
        $conference = $commService->scheduleConference(
            $this->parentId,
            $this->teacherId,
            $this->studentId,
            date('Y-m-d H:i:s', strtotime('+1 week'))
        );

        $updated = $commService->updateConferenceStatus(
            $this->parentId,
            $conference->id,
            'confirmed',
            'Confirming the meeting'
        );

        $this->assertEquals('confirmed', $updated->status);
        $this->assertEquals('Confirming the meeting', $updated->parent_notes);
    }

    public function test_parent_can_register_for_event()
    {
        $engagementService = new ParentEngagementService();

        $event = \App\Models\Calendar\CalendarEvent::factory()->create();
        $registration = $engagementService->registerForEvent(
            $this->parentId,
            $event->id,
            $this->studentId,
            2
        );

        $this->assertEquals($this->parentId, $registration->parent_id);
        $this->assertEquals($event->id, $registration->event_id);
        $this->assertEquals($this->studentId, $registration->student_id);
        $this->assertEquals(2, $registration->number_of_attendees);
        $this->assertEquals('registered', $registration->status);
    }

    public function test_parent_cannot_register_twice_for_same_event()
    {
        $this->expectException(\RuntimeException::class);

        $engagementService = new ParentEngagementService();
        $event = \App\Models\Calendar\CalendarEvent::factory()->create();

        $engagementService->registerForEvent($this->parentId, $event->id, $this->studentId);
        $engagementService->registerForEvent($this->parentId, $event->id, $this->studentId);
    }

    public function test_parent_can_update_notification_preferences()
    {
        $engagementService = new ParentEngagementService();
        $preferences = $engagementService->updateNotificationPreferences(
            $this->parentId,
            'grade',
            [
                'email_enabled' => true,
                'sms_enabled' => false,
                'push_enabled' => true,
                'in_app_enabled' => true,
            ]
        );

        $this->assertEquals($this->parentId, $preferences->parent_id);
        $this->assertEquals('grade', $preferences->notification_type);
        $this->assertTrue($preferences->email_enabled);
        $this->assertFalse($preferences->sms_enabled);
    }

    public function test_parent_can_get_engagement_metrics()
    {
        $engagementService = new ParentEngagementService();
        $engagementService->logEngagement($this->parentId, 'view_dashboard', 'Viewed student dashboard');
        $engagementService->logEngagement($this->parentId, 'view_grades', 'Viewed student grades');

        $metrics = $engagementService->getEngagementMetrics($this->parentId);

        $this->assertEquals(2, $metrics['total_actions']);
        $this->assertArrayHasKey('actions_by_type', $metrics);
        $this->assertArrayHasKey('engagement_score', $metrics);
    }

    public function test_parent_can_cancel_event_registration()
    {
        $engagementService = new ParentEngagementService();
        $event = \App\Models\Calendar\CalendarEvent::factory()->create();
        $registration = $engagementService->registerForEvent($this->parentId, $event->id, $this->studentId);

        $result = $engagementService->cancelEventRegistration($this->parentId, $event->id);

        $this->assertTrue($result);

        $registration->refresh();
        $this->assertEquals('cancelled', $registration->status);
    }
}
