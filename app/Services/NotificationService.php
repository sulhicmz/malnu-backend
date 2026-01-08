<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Notification\Notification;
use App\Models\Notification\NotificationDeliveryLog;
use App\Models\Notification\NotificationRecipient;
use App\Models\Notification\NotificationTemplate;
use App\Models\Notification\NotificationUserPreference;
use App\Models\User;
use Exception;
use Hyperf\DbConnection\Db;

class NotificationService
{
    public function createNotification(array $data): Notification
    {
        return Db::transaction(function () use ($data) {
            $notification = Notification::create([
                'template_id' => $data['template_id'] ?? null,
                'title' => $data['title'],
                'message' => $data['message'],
                'type' => $data['type'] ?? 'general',
                'priority' => $data['priority'] ?? 'medium',
                'channels' => $data['channels'] ?? ['email', 'sms', 'push', 'in_app'],
                'metadata' => $data['metadata'] ?? [],
                'sent_by' => $data['sent_by'] ?? null,
                'scheduled_at' => $data['scheduled_at'] ?? null,
            ]);

            foreach ($data['recipients'] as $userId) {
                $recipient = NotificationRecipient::create([
                    'notification_id' => $notification->id,
                    'user_id' => $userId,
                    'delivery_channels' => [],
                    'delivery_status' => [],
                ]);

                $preference = $this->getUserPreference($userId);
                $channels = $this->determineChannels($notification, $preference);

                foreach ($channels as $channel) {
                    NotificationDeliveryLog::create([
                        'notification_id' => $notification->id,
                        'recipient_id' => $recipient->id,
                        'channel' => $channel,
                        'status' => 'pending',
                        'retry_count' => 0,
                    ]);
                }

                $recipient->update([
                    'delivery_channels' => $channels,
                    'delivery_status' => array_fill_keys($channels, 'pending'),
                ]);
            }

            return $notification;
        });
    }

    public function sendNotification(Notification $notification): bool
    {
        foreach ($notification->recipients as $recipient) {
            foreach ($recipient->deliveryLogs as $log) {
                if ($log->status === 'pending') {
                    $this->processDelivery($log);
                }
            }
        }

        $notification->update([
            'sent_at' => now(),
        ]);

        return true;
    }

    public function sendBulkNotification(array $data): Notification
    {
        $notification = $this->createNotification($data);
        $this->sendNotification($notification);
        return $notification;
    }

    public function sendEmergencyNotification(array $data): Notification
    {
        $data['priority'] = 'critical';
        $data['channels'] = ['email', 'sms', 'push', 'in_app'];

        $notification = $this->createNotification($data);
        $this->sendNotification($notification);

        return $notification;
    }

    public function createTemplate(array $data): NotificationTemplate
    {
        return NotificationTemplate::create([
            'name' => $data['name'],
            'slug' => $data['slug'],
            'type' => $data['type'],
            'subject' => $data['subject'] ?? null,
            'body' => $data['body'],
            'variables' => $data['variables'] ?? [],
            'is_active' => $data['is_active'] ?? true,
            'created_by' => $data['created_by'] ?? null,
            'updated_by' => $data['updated_by'] ?? null,
        ]);
    }

    public function getUserNotifications(string $userId, array $filters = [])
    {
        $query = NotificationRecipient::with('notification')
            ->where('user_id', $userId);

        if (isset($filters['read'])) {
            $query->where('read', $filters['read']);
        }

        if (isset($filters['type'])) {
            $query->whereHas('notification', function ($q) use ($filters) {
                $q->where('type', $filters['type']);
            });
        }

        return $query->orderBy('created_at', 'desc')
            ->paginate($filters['per_page'] ?? 20);
    }

    public function markAsRead(string $notificationId, string $userId): bool
    {
        $recipient = NotificationRecipient::where('notification_id', $notificationId)
            ->where('user_id', $userId)
            ->first();

        if (! $recipient) {
            return false;
        }

        $recipient->update([
            'read' => true,
            'read_at' => now(),
        ]);

        return true;
    }

    public function markAllAsRead(string $userId): int
    {
        return NotificationRecipient::where('user_id', $userId)
            ->where('read', false)
            ->update([
                'read' => true,
                'read_at' => now(),
            ]);
    }

    public function updateUserPreference(string $userId, array $preferences): NotificationUserPreference
    {
        return NotificationUserPreference::updateOrCreate(
            ['user_id' => $userId],
            [
                'email_enabled' => $preferences['email_enabled'] ?? true,
                'sms_enabled' => $preferences['sms_enabled'] ?? true,
                'push_enabled' => $preferences['push_enabled'] ?? true,
                'in_app_enabled' => $preferences['in_app_enabled'] ?? true,
                'type_preferences' => $preferences['type_preferences'] ?? [],
                'quiet_hours' => $preferences['quiet_hours'] ?? null,
            ]
        );
    }

    public function getUserPreference(string $userId): NotificationUserPreference
    {
        return NotificationUserPreference::firstOrCreate(
            ['user_id' => $userId],
            [
                'email_enabled' => true,
                'sms_enabled' => true,
                'push_enabled' => true,
                'in_app_enabled' => true,
                'type_preferences' => [],
                'quiet_hours' => null,
            ]
        );
    }

    public function getDeliveryStats(Notification $notification): array
    {
        $logs = $notification->deliveryLogs;

        $stats = [
            'total' => $logs->count(),
            'pending' => 0,
            'sent' => 0,
            'delivered' => 0,
            'failed' => 0,
            'by_channel' => [
                'email' => ['pending' => 0, 'sent' => 0, 'delivered' => 0, 'failed' => 0],
                'sms' => ['pending' => 0, 'sent' => 0, 'delivered' => 0, 'failed' => 0],
                'push' => ['pending' => 0, 'sent' => 0, 'delivered' => 0, 'failed' => 0],
                'in_app' => ['pending' => 0, 'sent' => 0, 'delivered' => 0, 'failed' => 0],
            ],
        ];

        foreach ($logs as $log) {
            ++$stats[$log->status];
            ++$stats['by_channel'][$log->channel][$log->status];
        }

        return $stats;
    }

    public function processTemplate(NotificationTemplate $template, array $variables): string
    {
        $body = $template->body;

        foreach ($variables as $key => $value) {
            $body = str_replace('{' . $key . '}', $value, $body);
        }

        return $body;
    }

    public function sendScheduledNotifications(): int
    {
        $notifications = Notification::where('scheduled_at', '<=', now())
            ->whereNull('sent_at')
            ->get();

        foreach ($notifications as $notification) {
            $this->sendNotification($notification);
        }

        return $notifications->count();
    }

    private function determineChannels(Notification $notification, NotificationUserPreference $preference): array
    {
        if ($notification->priority === 'critical') {
            return ['email', 'sms', 'push', 'in_app'];
        }

        $channels = [];

        if ($preference->email_enabled && $this->isTypeEnabled('email', $notification, $preference)) {
            $channels[] = 'email';
        }

        if ($preference->sms_enabled && $this->isTypeEnabled('sms', $notification, $preference)) {
            $channels[] = 'sms';
        }

        if ($preference->push_enabled && $this->isTypeEnabled('push', $notification, $preference)) {
            $channels[] = 'push';
        }

        if ($preference->in_app_enabled && $this->isTypeEnabled('in_app', $notification, $preference)) {
            $channels[] = 'in_app';
        }

        return $channels;
    }

    private function isTypeEnabled(string $channel, Notification $notification, NotificationUserPreference $preference): bool
    {
        if (empty($preference->type_preferences)) {
            return true;
        }

        return $preference->type_preferences[$notification->type] ?? true;
    }

    private function processDelivery(NotificationDeliveryLog $log): void
    {
        try {
            $success = $this->deliverToChannel($log);

            if ($success) {
                $log->update([
                    'status' => 'delivered',
                    'delivered_at' => now(),
                ]);
            } else {
                $log->update([
                    'status' => 'failed',
                    'failed_at' => now(),
                ]);
            }
        } catch (Exception $e) {
            $log->update([
                'status' => 'failed',
                'failed_at' => now(),
                'error_message' => $e->getMessage(),
                'retry_count' => $log->retry_count + 1,
            ]);
        }
    }

    private function deliverToChannel(NotificationDeliveryLog $log): bool
    {
        $channel = $log->channel;
        $notification = $log->notification;
        $recipient = $log->recipient;
        $user = $recipient->user;

        switch ($channel) {
            case 'email':
                return $this->sendEmail($user, $notification);
            case 'sms':
                return $this->sendSMS($user, $notification);
            case 'push':
                return $this->sendPush($user, $notification);
            case 'in_app':
                return $this->saveInApp($user, $notification);
            default:
                return false;
        }
    }

    private function sendEmail(User $user, Notification $notification): bool
    {
        return true;
    }

    private function sendSMS(User $user, Notification $notification): bool
    {
        return true;
    }

    private function sendPush(User $user, Notification $notification): bool
    {
        return true;
    }

    private function saveInApp(User $user, Notification $notification): bool
    {
        return true;
    }
}
