<?php

declare(strict_types=1);

namespace App\Notifications;

use Hypervel\Notifications\Notification;

class MobileNotification extends Notification
{
    protected string $title;
    protected string $body;
    protected array $data;
    protected array $targets; // User IDs or device tokens

    public function __construct(string $title, string $body, array $data = [], array $targets = [])
    {
        $this->title = $title;
        $this->body = $body;
        $this->data = $data;
        $this->targets = $targets;
    }

    public function via($notifiable): array
    {
        // For now, return an empty array since we don't have a specific mobile notification channel
        // In a real implementation, this would include FCM, APNs, or database notifications
        return ['database']; // Using database as a placeholder
    }

    public function toArray($notifiable): array
    {
        return [
            'title' => $this->title,
            'body' => $this->body,
            'data' => $this->data,
            'targets' => $this->targets,
            'created_at' => now(),
        ];
    }
}