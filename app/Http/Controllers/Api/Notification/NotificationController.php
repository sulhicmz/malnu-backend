<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Notification;

use App\Http\Controllers\Api\BaseController;
use App\Models\Notification\Notification;
use App\Services\NotificationService;
use Exception;
use Hyperf\HttpServer\Annotation\Controller;
use Hyperf\HttpServer\Annotation\RequestMapping;
use Hyperf\HttpServer\Contract\RequestInterface;
use Psr\Http\Message\ResponseInterface;

#[Controller]
class NotificationController extends BaseController
{
    private NotificationService $notificationService;

    public function __construct(RequestInterface $request, \Hyperf\HttpServer\Contract\ResponseInterface $response, \Psr\Container\ContainerInterface $container)
    {
        parent::__construct($request, $response, $container);
        $this->notificationService = new NotificationService();
    }

    #[RequestMapping(path: '/api/notifications', methods: 'POST')]
    public function create(RequestInterface $request): ResponseInterface
    {
        $data = $this->request->all();

        if (empty($data['title']) || empty($data['message']) || empty($data['recipients'])) {
            return $this->validationErrorResponse([
                'title' => ['Title is required'],
                'message' => ['Message is required'],
                'recipients' => ['Recipients are required'],
            ]);
        }

        try {
            $notification = $this->notificationService->createNotification($data);
            return $this->successResponse($notification->toArray(), 'Notification created successfully', 201);
        } catch (Exception $e) {
            return $this->serverErrorResponse('Failed to create notification: ' . $e->getMessage());
        }
    }

    #[RequestMapping(path: '/api/notifications/send', methods: 'POST')]
    public function send(RequestInterface $request): ResponseInterface
    {
        $data = $this->request->all();

        if (empty($data['title']) || empty($data['message']) || empty($data['recipients'])) {
            return $this->validationErrorResponse([
                'title' => ['Title is required'],
                'message' => ['Message is required'],
                'recipients' => ['Recipients are required'],
            ]);
        }

        try {
            $notification = $this->notificationService->sendBulkNotification($data);
            return $this->successResponse($notification->toArray(), 'Notification sent successfully');
        } catch (Exception $e) {
            return $this->serverErrorResponse('Failed to send notification: ' . $e->getMessage());
        }
    }

    #[RequestMapping(path: '/api/notifications/emergency', methods: 'POST')]
    public function sendEmergency(RequestInterface $request): ResponseInterface
    {
        $data = $this->request->all();

        if (empty($data['title']) || empty($data['message']) || empty($data['recipients'])) {
            return $this->validationErrorResponse([
                'title' => ['Title is required'],
                'message' => ['Message is required'],
                'recipients' => ['Recipients are required'],
            ]);
        }

        try {
            $notification = $this->notificationService->sendEmergencyNotification($data);
            return $this->successResponse($notification->toArray(), 'Emergency notification sent successfully');
        } catch (Exception $e) {
            return $this->serverErrorResponse('Failed to send emergency notification: ' . $e->getMessage());
        }
    }

    #[RequestMapping(path: '/api/notifications/my', methods: 'GET')]
    public function getMyNotifications(RequestInterface $request): ResponseInterface
    {
        $userId = $this->request->input('user_id');

        if (! $userId) {
            return $this->validationErrorResponse(['user_id' => ['User ID is required']]);
        }

        $filters = [
            'read' => $this->request->input('read'),
            'type' => $this->request->input('type'),
            'per_page' => $this->request->input('per_page', 20),
        ];

        try {
            $notifications = $this->notificationService->getUserNotifications($userId, $filters);
            return $this->successResponse($notifications->toArray(), 'Notifications retrieved successfully');
        } catch (Exception $e) {
            return $this->serverErrorResponse('Failed to retrieve notifications: ' . $e->getMessage());
        }
    }

    #[RequestMapping(path: '/api/notifications/{id}/read', methods: 'PUT')]
    public function markAsRead(RequestInterface $request, $id): ResponseInterface
    {
        $userId = $this->request->input('user_id');

        if (! $userId) {
            return $this->validationErrorResponse(['user_id' => ['User ID is required']]);
        }

        try {
            $success = $this->notificationService->markAsRead($id, $userId);

            if (! $success) {
                return $this->notFoundResponse('Notification not found');
            }

            return $this->successResponse(null, 'Notification marked as read');
        } catch (Exception $e) {
            return $this->serverErrorResponse('Failed to mark notification as read: ' . $e->getMessage());
        }
    }

    #[RequestMapping(path: '/api/notifications/read-all', methods: 'PUT')]
    public function markAllAsRead(RequestInterface $request): ResponseInterface
    {
        $userId = $this->request->input('user_id');

        if (! $userId) {
            return $this->validationErrorResponse(['user_id' => ['User ID is required']]);
        }

        try {
            $count = $this->notificationService->markAllAsRead($userId);
            return $this->successResponse(['count' => $count], "{$count} notifications marked as read");
        } catch (Exception $e) {
            return $this->serverErrorResponse('Failed to mark notifications as read: ' . $e->getMessage());
        }
    }

    #[RequestMapping(path: '/api/notifications/{id}', methods: 'GET')]
    public function getNotification(RequestInterface $request, $id): ResponseInterface
    {
        try {
            $notification = Notification::with(['recipients', 'deliveryLogs'])->find($id);

            if (! $notification) {
                return $this->notFoundResponse('Notification not found');
            }

            return $this->successResponse($notification->toArray(), 'Notification retrieved successfully');
        } catch (Exception $e) {
            return $this->serverErrorResponse('Failed to retrieve notification: ' . $e->getMessage());
        }
    }

    #[RequestMapping(path: '/api/notifications/{id}/stats', methods: 'GET')]
    public function getStats(RequestInterface $request, $id): ResponseInterface
    {
        try {
            $notification = Notification::find($id);

            if (! $notification) {
                return $this->notFoundResponse('Notification not found');
            }

            $stats = $this->notificationService->getDeliveryStats($notification);
            return $this->successResponse($stats, 'Notification stats retrieved successfully');
        } catch (Exception $e) {
            return $this->serverErrorResponse('Failed to retrieve notification stats: ' . $e->getMessage());
        }
    }

    #[RequestMapping(path: '/api/notifications/templates', methods: 'POST')]
    public function createTemplate(RequestInterface $request): ResponseInterface
    {
        $data = $this->request->all();

        if (empty($data['name']) || empty($data['slug']) || empty($data['type']) || empty($data['body'])) {
            return $this->validationErrorResponse([
                'name' => ['Name is required'],
                'slug' => ['Slug is required'],
                'type' => ['Type is required'],
                'body' => ['Body is required'],
            ]);
        }

        try {
            $template = $this->notificationService->createTemplate($data);
            return $this->successResponse($template->toArray(), 'Template created successfully', 201);
        } catch (Exception $e) {
            return $this->serverErrorResponse('Failed to create template: ' . $e->getMessage());
        }
    }

    #[RequestMapping(path: '/api/notifications/preferences', methods: 'PUT')]
    public function updatePreferences(RequestInterface $request): ResponseInterface
    {
        $userId = $this->request->input('user_id');

        if (! $userId) {
            return $this->validationErrorResponse(['user_id' => ['User ID is required']]);
        }

        try {
            $preference = $this->notificationService->updateUserPreference($userId, $this->request->all());
            return $this->successResponse($preference->toArray(), 'Preferences updated successfully');
        } catch (Exception $e) {
            return $this->serverErrorResponse('Failed to update preferences: ' . $e->getMessage());
        }
    }

    #[RequestMapping(path: '/api/notifications/preferences', methods: 'GET')]
    public function getPreferences(RequestInterface $request): ResponseInterface
    {
        $userId = $this->request->input('user_id');

        if (! $userId) {
            return $this->validationErrorResponse(['user_id' => ['User ID is required']]);
        }

        try {
            $preference = $this->notificationService->getUserPreference($userId);
            return $this->successResponse($preference->toArray(), 'Preferences retrieved successfully');
        } catch (Exception $e) {
            return $this->serverErrorResponse('Failed to retrieve preferences: ' . $e->getMessage());
        }
    }
}
