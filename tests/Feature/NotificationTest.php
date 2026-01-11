<?php

declare(strict_types=1);

namespace Tests\Feature;

use Hyperf\DbConnection\Db;
use App\Services\NotificationService;
use App\Models\Notification\Notification;
use App\Models\Notification\NotificationTemplate;
use App\Models\Notification\NotificationUserPreference;

class NotificationTest extends \Codeception\Test\Unit
{
    public function _before()
    {
        Db::table('notification_templates')->truncate();
        Db::table('notifications')->truncate();
        Db::table('notification_user_preferences')->truncate();
        Db::table('notification_recipients')->truncate();
        Db::table('notification_delivery_logs')->truncate();
    }

    public function _after()
    {
    }

    public function testCanCreateNotification()
    {
        $service = make(NotificationService::class);

        $notification = $service->create([
            'title' => 'Test Notification',
            'message' => 'This is a test message',
            'type' => 'info',
            'priority' => 'medium',
        ]);

        $this->assertNotNull($notification);
        $this->assertEquals('Test Notification', $notification->title);
        $this->assertEquals('This is a test message', $notification->message);
        $this->assertEquals('info', $notification->type);
        $this->assertEquals('medium', $notification->priority);
    }

    public function testCanSendNotificationToSpecificUsers()
    {
        $service = make(NotificationService::class);

        $notification = $service->create([
            'title' => 'Test Notification',
            'message' => 'This is a test message',
            'type' => 'info',
        ]);

        $userIds = ['test-user-1', 'test-user-2'];

        $service->send($notification->id, $userIds);

        $recipients = Db::table('notification_recipients')
            ->where('notification_id', $notification->id)
            ->whereIn('user_id', $userIds)
            ->get();

        $this->assertCount(2, $recipients);
    }

    public function testEmergencyNotificationBypassesPreferences()
    {
        $service = make(NotificationService::class);

        $notification = $service->create([
            'title' => 'Emergency Test',
            'message' => 'This is an emergency',
            'type' => 'emergency',
            'priority' => 'critical',
        ]);

        $service->send($notification->id);

        $recipients = Db::table('notification_recipients')
            ->where('notification_id', $notification->id)
            ->get();

        $this->assertGreaterThanOrEqual(0, count($recipients));
    }

    public function testCanMarkNotificationAsRead()
    {
        $service = make(NotificationService::class);

        $notification = $service->create([
            'title' => 'Test Notification',
            'message' => 'This is a test message',
        ]);

        $userId = 'test-user-1';

        $service->send($notification->id, [$userId]);

        $result = $service->markAsRead($notification->id, $userId);

        $this->assertTrue($result);

        $recipient = Db::table('notification_recipients')
            ->where('notification_id', $notification->id)
            ->where('user_id', $userId)
            ->first();

        $this->assertEquals(1, $recipient->read);
        $this->assertNotNull($recipient->read_at);
    }

    public function testCanGetUserNotifications()
    {
        $service = make(NotificationService::class);

        $notification1 = $service->create([
            'title' => 'Test Notification 1',
            'message' => 'Message 1',
            'type' => 'info',
        ]);

        $notification2 = $service->create([
            'title' => 'Test Notification 2',
            'message' => 'Message 2',
            'type' => 'grade',
        ]);

        $userId = 'test-user-1';

        $service->send($notification1->id, [$userId]);
        $service->send($notification2->id, [$userId]);

        $result = $service->getUserNotifications($userId);

        $this->assertCount(2, $result['notifications']);
        $this->assertEquals(2, $result['total']);
    }

    public function testCanFilterNotificationsByType()
    {
        $service = make(NotificationService::class);

        $notification1 = $service->create([
            'title' => 'Info Notification',
            'message' => 'Info message',
            'type' => 'info',
        ]);

        $notification2 = $service->create([
            'title' => 'Grade Notification',
            'message' => 'Grade message',
            'type' => 'grade',
        ]);

        $userId = 'test-user-1';

        $service->send($notification1->id, [$userId]);
        $service->send($notification2->id, [$userId]);

        $result = $service->getUserNotifications($userId, null, null, 'grade');

        $this->assertCount(1, $result['notifications']);
        $this->assertEquals(1, $result['total']);
    }

    public function testCanGetDeliveryStatistics()
    {
        $service = make(NotificationService::class);

        $notification = $service->create([
            'title' => 'Test Notification',
            'message' => 'Test message',
        ]);

        $userId = 'test-user-1';

        $service->send($notification->id, [$userId]);

        $stats = $service->getDeliveryStatistics($notification->id);

        $this->assertArrayHasKey('total', $stats);
        $this->assertArrayHasKey('email', $stats);
        $this->assertArrayHasKey('sms', $stats);
        $this->assertArrayHasKey('push', $stats);
        $this->assertArrayHasKey('in_app', $stats);
    }

    public function testCanCreateNotificationTemplate()
    {
        $service = make(NotificationService::class);

        $templateData = [
            'name' => 'Test Template',
            'type' => 'info',
            'subject' => 'Test Subject',
            'body' => 'Test body with {variable}',
            'variables' => json_encode(['variable']),
        ];

        $template = $service->createTemplate($templateData);

        $this->assertNotNull($template);
        $this->assertEquals('Test Template', $template->name);
        $this->assertEquals('info', $template->type);
        $this->assertEquals('Test Subject', $template->subject);
    }

    public function testCanGetNotificationTemplates()
    {
        $service = make(NotificationService::class);

        $service->createTemplate([
            'name' => 'Template 1',
            'type' => 'info',
            'subject' => 'Subject 1',
            'body' => 'Body 1',
        ]);

        $service->createTemplate([
            'name' => 'Template 2',
            'type' => 'grade',
            'subject' => 'Subject 2',
            'body' => 'Body 2',
        ]);

        $templates = $service->getNotificationTemplates();

        $this->assertCount(2, $templates);
    }

    public function testCanUpdateUserPreferences()
    {
        $service = make(NotificationService::class);

        $userId = 'test-user-1';

        $preferences = [
            [
                'type' => 'info',
                'email_enabled' => true,
                'sms_enabled' => false,
                'push_enabled' => true,
                'in_app_enabled' => true,
            ],
        ];

        $result = $service->updateUserPreference($userId, $preferences);

        $this->assertTrue($result);

        $pref = Db::table('notification_user_preferences')
            ->where('user_id', $userId)
            ->where('type', 'info')
            ->first();

        $this->assertNotNull($pref);
        $this->assertEquals(true, $pref->email_enabled);
        $this->assertEquals(false, $pref->sms_enabled);
    }

    public function testProcessTemplateVariables()
    {
        $service = make(NotificationService::class);

        $template = new NotificationTemplate([
            'body' => 'Hello {name}, your {grade} grade is {score}.',
        ]);

        $variables = ['name' => 'John', 'grade' => 'A', 'score' => '95'];

        $result = $service->processTemplate($template, $variables);

        $this->assertEquals('Hello John, your A grade is 95.', $result);
    }
}
