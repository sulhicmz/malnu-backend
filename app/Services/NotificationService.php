<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Notification\Notification;
use App\Models\Notification\NotificationRecipient;
use App\Models\Notification\NotificationTemplate;
use App\Models\Notification\UserNotificationPreference;
use App\Models\User;
use Hyperf\Contract\ConfigInterface;
use Hyperf\Di\Annotation\Inject;
use Hyperf\Logger\LoggerFactory;
use Psr\Log\LoggerInterface;

class NotificationService
{
    #[Inject]
    protected ConfigInterface $config;

    protected LoggerInterface $logger;

    public function __construct(LoggerFactory $loggerFactory)
    {
        $this->logger = $loggerFactory->get('notification');
    }

    /**
     * Send a notification to specific users
     */
    public function sendNotification(
        string $title,
        string $content,
        array $userIds,
        string $type = 'general',
        string $priority = 'medium',
        array $channels = ['email'],
        array $data = [],
        string $notificationType = 'general'
    ): Notification {
        $notification = Notification::create([
            'title' => $title,
            'content' => $content,
            'type' => $type,
            'priority' => $priority,
            'channels' => $channels,
            'data' => $data,
        ]);

        // Create recipients
        foreach ($userIds as $userId) {
            $this->createRecipient($notification, $userId);
        }

        // Process the notification delivery
        $this->processNotification($notification);

        return $notification;
    }

    /**
     * Send a broadcast notification to all users
     */
    public function sendBroadcastNotification(
        string $title,
        string $content,
        string $type = 'general',
        string $priority = 'medium',
        array $channels = ['email'],
        array $data = []
    ): Notification {
        $notification = Notification::create([
            'title' => $title,
            'content' => $content,
            'type' => $type,
            'priority' => $priority,
            'channels' => $channels,
            'data' => $data,
            'is_broadcast' => true,
        ]);

        // Get all users and create recipients
        $users = User::all();
        foreach ($users as $user) {
            $this->createRecipient($notification, $user->id);
        }

        // Process the notification delivery
        $this->processNotification($notification);

        return $notification;
    }

    /**
     * Send notification using a template
     */
    public function sendNotificationFromTemplate(
        string $templateSlug,
        array $userIds,
        array $templateData = []
    ): ?Notification {
        $template = NotificationTemplate::where('slug', $templateSlug)
            ->where('is_active', true)
            ->first();

        if (!$template) {
            $this->logger->error("Notification template not found: {$templateSlug}");
            return null;
        }

        // Replace placeholders in the template
        $title = $this->replacePlaceholders($template->subject ?? $template->name, $templateData);
        $content = $this->replacePlaceholders($template->body, $templateData);

        return $this->sendNotification(
            $title,
            $content,
            $userIds,
            $template->type,
            'medium',
            $template->channels,
            $templateData
        );
    }

    /**
     * Create a notification recipient
     */
    protected function createRecipient(Notification $notification, string $userId): NotificationRecipient
    {
        return NotificationRecipient::create([
            'notification_id' => $notification->id,
            'user_id' => $userId,
            'delivery_status' => array_fill_keys($notification->channels, 'pending'),
        ]);
    }

    /**
     * Process notification delivery
     */
    protected function processNotification(Notification $notification): void
    {
        $recipients = $notification->recipients;

        foreach ($recipients as $recipient) {
            $this->deliverNotificationToRecipient($notification, $recipient);
        }
    }

    /**
     * Deliver notification to a specific recipient
     */
    protected function deliverNotificationToRecipient(Notification $notification, NotificationRecipient $recipient): void
    {
        $user = $recipient->user;
        $preferences = $this->getUserPreferences($user->id);

        foreach ($notification->channels as $channel) {
            // Check if user has this channel enabled
            $channelEnabled = $preferences["{$channel}_enabled"] ?? true;
            if (!$channelEnabled) {
                continue;
            }

            $success = $this->deliverViaChannel($notification, $user, $channel);
            
            if ($success) {
                $this->updateDeliveryStatus($recipient, $channel, 'sent');
            } else {
                $this->updateDeliveryStatus($recipient, $channel, 'failed');
            }
        }
    }

    /**
     * Deliver notification via specific channel
     */
    protected function deliverViaChannel(Notification $notification, User $user, string $channel): bool
    {
        try {
            switch ($channel) {
                case 'email':
                    return $this->sendEmail($user, $notification);
                case 'sms':
                    return $this->sendSms($user, $notification);
                case 'push':
                    return $this->sendPush($user, $notification);
                case 'in_app':
                    return $this->sendInApp($user, $notification);
                default:
                    $this->logger->warning("Unknown channel: {$channel}");
                    return false;
            }
        } catch (\Exception $e) {
            $this->logger->error("Failed to deliver notification via {$channel}: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Send email notification
     */
    protected function sendEmail(User $user, Notification $notification): bool
    {
        // This would integrate with Hyperf's mail system
        // For now, we'll log the action
        $this->logger->info("Email sent to {$user->email} with subject: {$notification->title}");
        return true; // Simulate success
    }

    /**
     * Send SMS notification
     */
    protected function sendSms(User $user, Notification $notification): bool
    {
        // This would integrate with an SMS service
        $this->logger->info("SMS sent to {$user->phone} with message: {$notification->title}");
        return true; // Simulate success
    }

    /**
     * Send push notification
     */
    protected function sendPush(User $user, Notification $notification): bool
    {
        // This would integrate with push notification service
        $this->logger->info("Push notification sent to user: {$user->id}");
        return true; // Simulate success
    }

    /**
     * Send in-app notification
     */
    protected function sendInApp(User $user, Notification $notification): bool
    {
        // For in-app notifications, we just mark them as delivered
        // They will be visible in the user's notification center
        return true;
    }

    /**
     * Update delivery status for a recipient
     */
    protected function updateDeliveryStatus(NotificationRecipient $recipient, string $channel, string $status): void
    {
        $deliveryStatus = $recipient->delivery_status;
        $deliveryStatus[$channel] = $status;
        
        $recipient->update([
            'delivery_status' => $deliveryStatus
        ]);
    }

    /**
     * Get user notification preferences
     */
    protected function getUserPreferences(string $userId): array
    {
        $preferences = UserNotificationPreference::where('user_id', $userId)->first();
        
        if (!$preferences) {
            // Return default preferences
            return [
                'email_enabled' => true,
                'sms_enabled' => true,
                'push_enabled' => true,
                'in_app_enabled' => true,
            ];
        }

        return [
            'email_enabled' => $preferences->email_enabled,
            'sms_enabled' => $preferences->sms_enabled,
            'push_enabled' => $preferences->push_enabled,
            'in_app_enabled' => $preferences->in_app_enabled,
        ];
    }

    /**
     * Replace placeholders in template
     */
    protected function replacePlaceholders(string $text, array $data): string
    {
        foreach ($data as $key => $value) {
            $text = str_replace("{{{$key}}}", $value, $text);
        }
        return $text;
    }

    /**
     * Mark notification as read
     */
    public function markAsRead(string $notificationId, string $userId): bool
    {
        $recipient = NotificationRecipient::where('notification_id', $notificationId)
            ->where('user_id', $userId)
            ->first();

        if (!$recipient) {
            return false;
        }

        $recipient->update([
            'read_at' => now(),
        ]);

        // Also mark the notification as read if it's an in-app notification
        $notification = $recipient->notification;
        if (!$notification->is_read) {
            $notification->update(['is_read' => true]);
        }

        return true;
    }

    /**
     * Get user notifications
     */
    public function getUserNotifications(string $userId, int $limit = 20, int $offset = 0, string $status = 'all')
    {
        $query = Notification::join('notification_recipients', 'notifications.id', '=', 'notification_recipients.notification_id')
            ->where('notification_recipients.user_id', $userId)
            ->select('notifications.*', 'notification_recipients.read_at');

        switch ($status) {
            case 'unread':
                $query->whereNull('notification_recipients.read_at');
                break;
            case 'read':
                $query->whereNotNull('notification_recipients.read_at');
                break;
        }

        return $query->orderBy('notifications.created_at', 'desc')
            ->offset($offset)
            ->limit($limit)
            ->get();
    }
}