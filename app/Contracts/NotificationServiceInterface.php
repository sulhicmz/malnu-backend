<?php

declare(strict_types=1);

namespace App\Contracts;

use App\Models\Notification\Notification;
use App\Models\Notification\NotificationTemplate;
use App\Models\Notification\NotificationUserPreference;

interface NotificationServiceInterface
{
    public function create(array $data): Notification;

    public function send(string $notificationId, ?array $specificUserIds = null): void;

    public function processTemplate(NotificationTemplate $template, array $variables = []): string;

    public function markAsRead(string $notificationId, string $userId): bool;

    public function getUserNotifications(string $userId, ?int $limit = 20, ?int $offset = 0): array;

    public function getDeliveryStatistics(string $notificationId): array;

    public function getUserPreference(string $userId, string $type): ?NotificationUserPreference;

    public function updateUserPreference(string $userId, array $preferences): bool;

    public function getNotificationTemplates(?string $type = null): array;
}
