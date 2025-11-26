<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\BaseController;
use App\Models\Notification\Notification;
use App\Models\Notification\NotificationTemplate;
use App\Models\User;
use App\Services\NotificationService;
use Hyperf\HttpServer\Contract\RequestInterface;
use Hyperf\HttpServer\Contract\ResponseInterface;

class NotificationController extends BaseController
{
    protected NotificationService $notificationService;

    public function __construct(NotificationService $notificationService)
    {
        $this->notificationService = $notificationService;
    }

    /**
     * Get user notifications
     */
    public function index(RequestInterface $request, ResponseInterface $response)
    {
        try {
            $user = $this->getUserFromToken($request);
            if (!$user) {
                return $response->json(['error' => 'Unauthorized'], 401);
            }

            $limit = (int) $request->input('limit', 20);
            $offset = (int) $request->input('offset', 0);
            $status = $request->input('status', 'all'); // all, unread, read

            $notifications = $this->notificationService->getUserNotifications(
                $user->id,
                $limit,
                $offset,
                $status
            );

            return $response->json([
                'success' => true,
                'data' => $notifications,
                'pagination' => [
                    'limit' => $limit,
                    'offset' => $offset,
                    'total' => $notifications->count(), // This should be the total count, not just the current page
                ]
            ]);
        } catch (\Exception $e) {
            return $response->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Send a notification
     */
    public function send(RequestInterface $request, ResponseInterface $response)
    {
        try {
            $user = $this->getUserFromToken($request);
            if (!$user) {
                return $response->json(['error' => 'Unauthorized'], 401);
            }

            $title = $request->input('title');
            $content = $request->input('content');
            $userIds = $request->input('user_ids', []);
            $type = $request->input('type', 'general');
            $priority = $request->input('priority', 'medium');
            $channels = $request->input('channels', ['email']);
            $data = $request->input('data', []);

            if (empty($title) || empty($content)) {
                return $response->json(['error' => 'Title and content are required'], 400);
            }

            if (empty($userIds)) {
                return $response->json(['error' => 'At least one user ID is required'], 400);
            }

            $notification = $this->notificationService->sendNotification(
                $title,
                $content,
                $userIds,
                $type,
                $priority,
                $channels,
                $data
            );

            return $response->json([
                'success' => true,
                'data' => $notification
            ]);
        } catch (\Exception $e) {
            return $response->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Send a broadcast notification
     */
    public function broadcast(RequestInterface $request, ResponseInterface $response)
    {
        try {
            $user = $this->getUserFromToken($request);
            if (!$user) {
                return $response->json(['error' => 'Unauthorized'], 401);
            }

            // Only allow admin users to send broadcast notifications
            if (!$user->hasRole('admin')) {
                return $response->json(['error' => 'Unauthorized to send broadcast notifications'], 403);
            }

            $title = $request->input('title');
            $content = $request->input('content');
            $type = $request->input('type', 'general');
            $priority = $request->input('priority', 'medium');
            $channels = $request->input('channels', ['email']);
            $data = $request->input('data', []);

            if (empty($title) || empty($content)) {
                return $response->json(['error' => 'Title and content are required'], 400);
            }

            $notification = $this->notificationService->sendBroadcastNotification(
                $title,
                $content,
                $type,
                $priority,
                $channels,
                $data
            );

            return $response->json([
                'success' => true,
                'data' => $notification
            ]);
        } catch (\Exception $e) {
            return $response->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Send notification from template
     */
    public function sendFromTemplate(RequestInterface $request, ResponseInterface $response)
    {
        try {
            $user = $this->getUserFromToken($request);
            if (!$user) {
                return $response->json(['error' => 'Unauthorized'], 401);
            }

            $templateSlug = $request->input('template_slug');
            $userIds = $request->input('user_ids', []);
            $templateData = $request->input('template_data', []);

            if (empty($templateSlug)) {
                return $response->json(['error' => 'Template slug is required'], 400);
            }

            if (empty($userIds)) {
                return $response->json(['error' => 'At least one user ID is required'], 400);
            }

            $notification = $this->notificationService->sendNotificationFromTemplate(
                $templateSlug,
                $userIds,
                $templateData
            );

            if (!$notification) {
                return $response->json(['error' => 'Template not found or inactive'], 404);
            }

            return $response->json([
                'success' => true,
                'data' => $notification
            ]);
        } catch (\Exception $e) {
            return $response->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Mark notification as read
     */
    public function markAsRead(string $id, RequestInterface $request, ResponseInterface $response)
    {
        try {
            $user = $this->getUserFromToken($request);
            if (!$user) {
                return $response->json(['error' => 'Unauthorized'], 401);
            }

            $success = $this->notificationService->markAsRead($id, $user->id);

            if (!$success) {
                return $response->json(['error' => 'Notification not found or already read'], 404);
            }

            return $response->json([
                'success' => true,
                'message' => 'Notification marked as read'
            ]);
        } catch (\Exception $e) {
            return $response->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Get notification templates
     */
    public function getTemplates(RequestInterface $request, ResponseInterface $response)
    {
        try {
            $user = $this->getUserFromToken($request);
            if (!$user) {
                return $response->json(['error' => 'Unauthorized'], 401);
            }

            $templates = NotificationTemplate::where('is_active', true)->get();

            return $response->json([
                'success' => true,
                'data' => $templates
            ]);
        } catch (\Exception $e) {
            return $response->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Get user preferences
     */
    public function getPreferences(RequestInterface $request, ResponseInterface $response)
    {
        try {
            $user = $this->getUserFromToken($request);
            if (!$user) {
                return $response->json(['error' => 'Unauthorized'], 401);
            }

            $preferences = $user->notificationPreferences()->first();

            if (!$preferences) {
                // Return default preferences if none exist
                return $response->json([
                    'success' => true,
                    'data' => [
                        'email_enabled' => true,
                        'sms_enabled' => true,
                        'push_enabled' => true,
                        'in_app_enabled' => true,
                        'timezone' => 'UTC',
                        'preferences' => []
                    ]
                ]);
            }

            return $response->json([
                'success' => true,
                'data' => $preferences
            ]);
        } catch (\Exception $e) {
            return $response->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Update user preferences
     */
    public function updatePreferences(RequestInterface $request, ResponseInterface $response)
    {
        try {
            $user = $this->getUserFromToken($request);
            if (!$user) {
                return $response->json(['error' => 'Unauthorized'], 401);
            }

            $emailEnabled = $request->input('email_enabled', true);
            $smsEnabled = $request->input('sms_enabled', true);
            $pushEnabled = $request->input('push_enabled', true);
            $inAppEnabled = $request->input('in_app_enabled', true);
            $timezone = $request->input('timezone', 'UTC');
            $preferences = $request->input('preferences', []);

            $userPreferences = $user->notificationPreferences()->first();

            if ($userPreferences) {
                $userPreferences->update([
                    'email_enabled' => $emailEnabled,
                    'sms_enabled' => $smsEnabled,
                    'push_enabled' => $pushEnabled,
                    'in_app_enabled' => $inAppEnabled,
                    'timezone' => $timezone,
                    'preferences' => $preferences
                ]);
            } else {
                $user->notificationPreferences()->create([
                    'email_enabled' => $emailEnabled,
                    'sms_enabled' => $smsEnabled,
                    'push_enabled' => $pushEnabled,
                    'in_app_enabled' => $inAppEnabled,
                    'timezone' => $timezone,
                    'preferences' => $preferences
                ]);
            }

            return $response->json([
                'success' => true,
                'message' => 'Preferences updated successfully'
            ]);
        } catch (\Exception $e) {
            return $response->json(['error' => $e->getMessage()], 500);
        }
    }
}