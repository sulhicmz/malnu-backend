<?php

declare(strict_types=1);

namespace App\Services;

use App\Contracts\NotificationServiceInterface;
use App\Models\Notification\Notification;
use App\Models\Notification\NotificationDeliveryLog;
use App\Models\Notification\NotificationRecipient;
use App\Models\Notification\NotificationTemplate;
use App\Models\Notification\NotificationUserPreference;
use App\Models\User;
use DateTime;
use Exception;
use Hyperf\Di\Annotation\Inject;

class NotificationService implements NotificationServiceInterface
{
    #[Inject]
    private Notification $notificationModel;

    #[Inject]
    private NotificationTemplate $notificationTemplateModel;

    #[Inject]
    private NotificationRecipient $notificationRecipientModel;

    #[Inject]
    private NotificationDeliveryLog $notificationDeliveryLogModel;

    #[Inject]
    private NotificationUserPreference $notificationUserPreferenceModel;

    private array $commonPasswords = [
        'password', '123456', 'qwerty', 'abc123', 'password123',
        'admin', 'letmein', 'welcome', 'monkey', 'dragon',
        'master', 'hello', 'login', 'test', 'pass',
    ];

    public function create(array $data): Notification
    {
        return $this->notificationModel::create([
            'template_id' => $data['template_id'] ?? null,
            'title' => $data['title'] ?? '',
            'message' => $data['message'] ?? '',
            'type' => $data['type'] ?? 'info',
            'priority' => $data['priority'] ?? 'medium',
            'data' => $data['data'] ?? null,
            'scheduled_at' => $data['scheduled_at'] ?? null,
        ]);
    }

    public function send(string $notificationId, ?array $specificUserIds = null): void
    {
        $notification = $this->notificationModel::find($notificationId);
        if (! $notification) {
            throw new Exception('Notification not found');
        }

        $isEmergency = $notification->priority === 'critical';

        if ($specificUserIds) {
            $userIds = $specificUserIds;
        } elseif ($isEmergency) {
            $userIds = User::where('status', 'active')->pluck('id')->toArray();
        } else {
            $userIds = $this->getEligibleUserIds($notification);
        }

        foreach ($userIds as $userId) {
            $this->notificationRecipientModel::create([
                'notification_id' => $notificationId,
                'user_id' => $userId,
            ]);

            $userPreference = $this->notificationUserPreferenceModel
                ->where('user_id', $userId)
                ->where('type', $notification->type)
                ->first();

            $channels = $isEmergency
                ? ['email', 'sms', 'push', 'in_app']
                : $this->getEnabledChannels($userPreference, $notification->type);

            $currentTime = date('Y-m-d H:i:s');
            $isQuietHours = $this->isInQuietHours($userPreference, $currentTime);

            foreach ($channels as $channel) {
                if (! $isQuietHours || $isEmergency) {
                    $this->sendToChannel($channel, $notification, $userId);
                }
            }
        }
    }

    public function processTemplate(NotificationTemplate $template, array $variables = []): string
    {
        $message = $template->body;

        foreach ($variables as $key => $value) {
            $message = str_replace('{' . $key . '}', $value, $message);
        }

        return $message;
    }

    public function markAsRead(string $notificationId, string $userId): bool
    {
        $recipient = $this->notificationRecipientModel
            ->where('notification_id', $notificationId)
            ->where('user_id', $userId)
            ->first();

        if (! $recipient) {
            return false;
        }

        if ($recipient->read) {
            return true;
        }

        $recipient->update([
            'read' => true,
            'read_at' => now(),
        ]);

        return true;
    }

    public function getUserNotifications(string $userId, ?int $limit = 20, ?int $offset = 0): array
    {
        $query = $this->notificationRecipient::with('notification')
            ->where('user_id', $userId)
            ->orderBy('created_at', 'desc');

        $total = $query->count();
        $recipients = $query->offset($offset)->limit($limit)->get();

        return [
            'notifications' => $recipients,
            'total' => $total,
            'offset' => $offset,
            'limit' => $limit,
        ];
    }

    public function getDeliveryStatistics(string $notificationId): array
    {
        $logs = $this->notificationDeliveryLogModel::where('notification_id', $notificationId)->get();

        $stats = [
            'total' => $logs->count(),
            'email' => ['sent' => 0, 'delivered' => 0, 'failed' => 0],
            'sms' => ['sent' => 0, 'delivered' => 0, 'failed' => 0],
            'push' => ['sent' => 0, 'delivered' => 0, 'failed' => 0],
            'in_app' => ['sent' => 0, 'delivered' => 0, 'failed' => 0],
        ];

        foreach ($logs as $log) {
            if (isset($stats[$log->channel])) {
                ++$stats[$log->channel][$log->status];
            }
        }

        return $stats;
    }

    public function getUserPreference(string $userId, string $type): ?NotificationUserPreference
    {
        return $this->notificationUserPreferenceModel
            ->where('user_id', $userId)
            ->where('type', $type)
            ->first();
    }

    public function updateUserPreference(string $userId, array $preferences): bool
    {
        foreach ($preferences as $pref) {
            $existing = $this->notificationUserPreferenceModel
                ->where('user_id', $userId)
                ->where('type', $pref['type'])
                ->first();

            $data = [
                'email_enabled' => $pref['email_enabled'] ?? true,
                'sms_enabled' => $pref['sms_enabled'] ?? true,
                'push_enabled' => $pref['push_enabled'] ?? true,
                'in_app_enabled' => $pref['in_app_enabled'] ?? true,
                'quiet_hours_start' => $pref['quiet_hours_start'] ?? null,
                'quiet_hours_end' => $pref['quiet_hours_end'] ?? null,
            ];

            if ($existing) {
                $existing->update($data);
            } else {
                $data['user_id'] = $userId;
                $data['type'] = $pref['type'];
                $this->notificationUserPreferenceModel::create($data);
            }
        }

        return true;
    }

    public function getNotificationTemplates(?string $type = null): array
    {
        $query = $this->notificationTemplateModel->where('is_active', true);

        if ($type) {
            $query->where('type', $type);
        }

        return $query->orderBy('name', 'asc')->get();
    }

    private function getEligibleUserIds(Notification $notification): array
    {
        $userIds = [];

        switch ($notification->type) {
            case 'attendance':
            case 'grade':
            case 'event':
            case 'exam':
                $userIds = User::where('status', 'active')->pluck('id')->toArray();
                break;
            default:
                $userIds = User::where('status', 'active')->pluck('id')->toArray();
                break;
        }

        return $userIds;
    }

    private function getEnabledChannels(?NotificationUserPreference $preference, string $type): array
    {
        if (! $preference) {
            return ['in_app'];
        }

        $preference = $this->notificationUserPreferenceModel
            ->where('user_id', $preference->user_id)
            ->where('type', $type)
            ->first();

        if (! $preference) {
            return ['email', 'sms', 'push', 'in_app'];
        }

        $channels = [];
        if ($preference->email_enabled) {
            $channels[] = 'email';
        }
        if ($preference->sms_enabled) {
            $channels[] = 'sms';
        }
        if ($preference->push_enabled) {
            $channels[] = 'push';
        }
        if ($preference->in_app_enabled) {
            $channels[] = 'in_app';
        }

        return $channels ?: ['in_app'];
    }

    private function isInQuietHours(?NotificationUserPreference $preference, string $currentTime): bool
    {
        if (! $preference || ! $preference->quiet_hours_start || ! $preference->quiet_hours_end) {
            return false;
        }

        $currentTimeObj = DateTime::createFromFormat('Y-m-d H:i:s', $currentTime);
        $startTime = DateTime::createFromFormat('H:i:s', $preference->quiet_hours_start);
        $endTime = DateTime::createFromFormat('H:i:s', $preference->quiet_hours_end);

        // Validate DateTime objects were created successfully
        if (! $currentTimeObj || ! $startTime || ! $endTime) {
            return false;
        }

        if ($startTime > $endTime) {
            return ! ($currentTimeObj >= $startTime || $currentTimeObj <= $endTime);
        }

        return $currentTimeObj >= $startTime && $currentTimeObj <= $endTime;
    }

    private function sendToChannel(string $channel, Notification $notification, string $userId): void
    {
        try {
            switch ($channel) {
                case 'email':
                    $this->sendEmail($notification, $userId);
                    $status = 'sent';
                    break;
                case 'sms':
                    $this->sendSMS($notification, $userId);
                    $status = 'sent';
                    break;
                case 'push':
                    $this->sendPush($notification, $userId);
                    $status = 'sent';
                    break;
                case 'in_app':
                    $status = 'delivered';
                    break;
                default:
                    $status = 'failed';
                    break;
            }

            $recipient = $this->notificationRecipientModel
                ->where('notification_id', $notification->id)
                ->where('user_id', $userId)
                ->first();

            if ($recipient) {
                $this->notificationDeliveryLogModel::create([
                    'notification_id' => $notification->id,
                    'recipient_id' => $recipient->id,
                    'channel' => $channel,
                    'status' => $status,
                    'sent_at' => now(),
                ]);

                if ($channel === 'in_app' && $status === 'delivered') {
                    $recipient->update([
                        'read' => true,
                        'read_at' => now(),
                    ]);
                }
            }
        } catch (Exception $e) {
            $recipient = $this->notificationRecipientModel
                ->where('notification_id', $notification->id)
                ->where('user_id', $userId)
                ->first();

            if ($recipient) {
                $this->notificationDeliveryLogModel::create([
                    'notification_id' => $notification->id,
                    'recipient_id' => $recipient->id,
                    'channel' => $channel,
                    'status' => 'failed',
                    'error_message' => $e->getMessage(),
                    'sent_at' => now(),
                ]);
            }
        }
    }

    private function sendEmail(Notification $notification, string $userId): void
    {
    }

    private function sendSMS(Notification $notification, string $userId): void
    {
    }

    private function sendPush(Notification $notification, string $userId): void
    {
    }
}
