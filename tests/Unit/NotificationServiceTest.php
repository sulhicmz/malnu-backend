<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Services\NotificationService;
use App\Models\Notification\Notification;
use App\Models\Notification\NotificationRecipient;
use App\Models\Notification\NotificationUserPreference;
use App\Models\User;
use Tests\TestCase;

/**
 * @internal
 * @covers \App\Services\NotificationService
 */
class NotificationServiceTest extends TestCase
{
    private NotificationService $notificationService;

    private $mockNotificationModel;
    private $mockNotificationRecipientModel;
    private $mockNotificationUserPreferenceModel;

    protected function setUp(): void
    {
        parent::setUp();

        $this->notificationService = new NotificationService();
        $this->mockNotificationModel = $this->createMock(Notification::class);
        $this->mockNotificationRecipientModel = $this->createMock(NotificationRecipient::class);
        $this->mockNotificationUserPreferenceModel = $this->createMock(NotificationUserPreference::class);
    }

    public function testCreateNotification(): void
    {
        $data = [
            'template_id' => 'template123',
            'title' => 'Test Notification',
            'message' => 'This is a test message',
            'type' => 'info',
            'priority' => 'high',
            'data' => ['key' => 'value'],
            'scheduled_at' => '2026-02-10 10:00:00',
        ];

        $this->mockNotificationModel->expects($this->once())
            ->method('create')
            ->with($data)
            ->willReturn(new Notification($data));

        $result = $this->notificationService->create($data);

        $this->assertInstanceOf(Notification::class, $result);
    }

    public function testCreateNotificationWithDefaults(): void
    {
        $data = [
            'title' => 'Default Notification',
            'message' => 'Message with defaults',
        ];

        $this->mockNotificationModel->expects($this->once())
            ->method('create')
            ->with($this->callback(function ($actualData) {
                return isset($actualData['type']) && $actualData['type'] === 'info';
            }))
            ->willReturn(new Notification($data));

        $result = $this->notificationService->create($data);

        $this->assertInstanceOf(Notification::class, $result);
    }

    public function testCreateNotificationWithComplexData(): void
    {
        $data = [
            'title' => 'Complex Notification',
            'message' => 'Complex message content',
            'type' => 'emergency',
            'priority' => 'critical',
            'data' => [
                'event_id' => 'evt123',
                'event_type' => 'meeting',
                'location' => 'Room A',
                'attendees' => ['user1', 'user2', 'user3'],
            ],
        ];

        $this->mockNotificationModel->expects($this->once())
            ->method('create')
            ->with($data)
            ->willReturn(new Notification($data));

        $result = $this->notificationService->create($data);

        $this->assertInstanceOf(Notification::class, $result);
    }

    public function testCreateNotificationWithScheduledTime(): void
    {
        $data = [
            'title' => 'Scheduled Notification',
            'message' => 'This is scheduled',
            'scheduled_at' => '2026-03-01 09:00:00',
        ];

        $this->mockNotificationModel->expects($this->once())
            ->method('create')
            ->with($data)
            ->willReturn(new Notification($data));

        $result = $this->notificationService->create($data);

        $this->assertInstanceOf(Notification::class, $result);
    }
}
