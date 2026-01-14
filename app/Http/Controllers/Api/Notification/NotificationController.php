<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Notification;

use App\Contracts\NotificationServiceInterface;
use App\Http\Controllers\Api\BaseController;
use App\Models\Notification\NotificationTemplate;
use Exception;
use Hyperf\Di\Annotation\Inject;
use Hyperf\HttpServer\Contract\RequestInterface;
use Hyperf\HttpServer\Contract\ResponseInterface;
use Psr\Container\ContainerInterface;

class NotificationController extends BaseController
{
    #[Inject]
    private NotificationServiceInterface $notificationService;

    #[Inject]
    private NotificationTemplate $notificationTemplateModel;

    public function __construct(
        RequestInterface $request,
        ResponseInterface $response,
        ContainerInterface $container
    ) {
        parent::__construct($request, $response, $container);
    }

    public function create()
    {
        try {
            $data = $this->request->all();

            $errors = [];
            if (empty($data['title'])) {
                $errors['title'] = ['The title field is required.'];
            }
            if (empty($data['message'])) {
                $errors['message'] = ['The message field is required.'];
            }
            if (empty($data['type'])) {
                $errors['type'] = ['The type field is required.'];
            } elseif (! in_array($data['type'], ['info', 'high', 'medium', 'low', 'critical'])) {
                $errors['type'] = ['The type must be one of: info, high, medium, low, critical.'];
            }

            if (! empty($errors)) {
                return $this->validationErrorResponse($errors);
            }

            $notification = $this->notificationService->create($data);

            return $this->successResponse($notification, 'Notification created successfully', 201);
        } catch (Exception $e) {
            return $this->serverErrorResponse($e->getMessage(), 'NOTIFICATION_CREATION_ERROR');
        }
    }

    public function send()
    {
        try {
            $data = $this->request->all();

            $errors = [];
            if (empty($data['notification_id'])) {
                $errors['notification_id'] = ['The notification_id field is required.'];
            }
            if (! empty($data['user_ids']) && ! is_array($data['user_ids'])) {
                $errors['user_ids'] = ['The user_ids field must be an array.'];
            }

            if (! empty($errors)) {
                return $this->validationErrorResponse($errors);
            }

            $this->notificationService->send($data['notification_id'], $data['user_ids'] ?? null);

            return $this->successResponse(null, 'Notification sent successfully');
        } catch (Exception $e) {
            return $this->serverErrorResponse($e->getMessage(), 'NOTIFICATION_SEND_ERROR');
        }
    }

    public function sendEmergency()
    {
        try {
            $data = $this->request->all();

            $errors = [];
            if (empty($data['title'])) {
                $errors['title'] = ['The title field is required for emergency notifications.'];
            }
            if (empty($data['message'])) {
                $errors['message'] = ['The message field is required for emergency notifications.'];
            }
            if (! empty($errors)) {
                return $this->validationErrorResponse($errors);
            }

            $notification = $this->notificationService->create(array_merge($data, [
                'type' => 'critical',
                'priority' => 'critical',
            ]));

            $this->notificationService->send($notification->id, null);

            return $this->successResponse($notification, 'Emergency notification sent successfully', 201);
        } catch (Exception $e) {
            return $this->serverErrorResponse($e->getMessage(), 'EMERGENCY_NOTIFICATION_ERROR');
        }
    }

    public function index()
    {
        try {
            $userId = $this->request->getAttribute('jwt_user_id');
            $limit = (int) $this->request->query('limit', 20);
            $offset = (int) $this->request->query('offset', 0);
            $type = $this->request->query('type');
            $read = $this->request->query('read');

            $result = $this->notificationService->getUserNotifications($userId, $limit, $offset);

            if ($type) {
                $result['notifications'] = array_filter($result['notifications'], function ($item) {
                    return $item['notification']->type === $type;
                });
            }

            if ($read !== null) {
                $readFilter = $read === 'true' || $read === '1';
                $result['notifications'] = array_filter($result['notifications'], function ($item) use ($readFilter) {
                    return $item['read'] === $readFilter;
                });
            }

            return $this->successResponse($result);
        } catch (Exception $e) {
            return $this->serverErrorResponse($e->getMessage());
        }
    }

    public function show(string $id)
    {
        try {
            $recipient = \App\Models\Notification\NotificationRecipient::with('notification')->where('id', $id)->first();

            if (! $recipient) {
                return $this->notFoundResponse('Notification not found');
            }

            return $this->successResponse($recipient);
        } catch (Exception $e) {
            return $this->serverErrorResponse($e->getMessage());
        }
    }

    public function markAsRead(string $id)
    {
        try {
            $userId = $this->request->getAttribute('jwt_user_id');
            $result = $this->notificationService->markAsRead($id, $userId);

            if (! $result) {
                return $this->notFoundResponse('Notification not found');
            }

            return $this->successResponse(null, 'Notification marked as read');
        } catch (Exception $e) {
            return $this->serverErrorResponse($e->getMessage(), 'MARK_READ_ERROR');
        }
    }

    public function markAllAsRead()
    {
        try {
            $userId = $this->request->getAttribute('jwt_user_id');
            $recipients = \App\Models\Notification\NotificationRecipient::where('user_id', $userId)
                ->where('read', false)
                ->get();

            foreach ($recipients as $recipient) {
                $recipient->update([
                    'read' => true,
                    'read_at' => now(),
                ]);
            }

            return $this->successResponse(null, 'All notifications marked as read');
        } catch (Exception $e) {
            return $this->serverErrorResponse($e->getMessage(), 'MARK_ALL_READ_ERROR');
        }
    }

    public function getDeliveryStats(string $id)
    {
        try {
            $stats = $this->notificationService->getDeliveryStatistics($id);

            if (! $stats) {
                return $this->notFoundResponse('Notification not found');
            }

            return $this->successResponse($stats);
        } catch (Exception $e) {
            return $this->serverErrorResponse($e->getMessage());
        }
    }

    public function createTemplate()
    {
        try {
            $data = $this->request->all();

            $errors = [];
            if (empty($data['name'])) {
                $errors['name'] = ['The name field is required.'];
            }
            if (empty($data['type'])) {
                $errors['type'] = ['The type field is required.'];
            }
            if (empty($data['subject'])) {
                $errors['subject'] = ['The subject field is required.'];
            }
            if (empty($data['body'])) {
                $errors['body'] = ['The body field is required.'];
            }

            if (! empty($errors)) {
                return $this->validationErrorResponse($errors);
            }

            $template = $this->notificationTemplateModel::create($data);

            return $this->successResponse($template, 'Template created successfully', 201);
        } catch (Exception $e) {
            return $this->serverErrorResponse($e->getMessage(), 'TEMPLATE_CREATION_ERROR');
        }
    }

    public function getTemplates()
    {
        try {
            $type = $this->request->query('type');
            $templates = $this->notificationService->getNotificationTemplates($type);

            return $this->successResponse($templates);
        } catch (Exception $e) {
            return $this->serverErrorResponse($e->getMessage());
        }
    }

    public function updatePreferences()
    {
        try {
            $userId = $this->request->getAttribute('jwt_user_id');
            $data = $this->request->all();

            $errors = [];
            if (empty($data['type'])) {
                $errors['type'] = ['The type field is required.'];
            }

            if (! empty($errors)) {
                return $this->validationErrorResponse($errors);
            }

            $preferences = [
                'type' => $data['type'],
                'email_enabled' => $data['email_enabled'] ?? true,
                'sms_enabled' => $data['sms_enabled'] ?? true,
                'push_enabled' => $data['push_enabled'] ?? true,
                'in_app_enabled' => $data['in_app_enabled'] ?? true,
                'quiet_hours_start' => $data['quiet_hours_start'] ?? null,
                'quiet_hours_end' => $data['quiet_hours_end'] ?? null,
            ];

            $this->notificationService->updateUserPreference($userId, [$preferences]);

            return $this->successResponse(null, 'Preferences updated successfully');
        } catch (Exception $e) {
            return $this->serverErrorResponse($e->getMessage(), 'PREFERENCE_UPDATE_ERROR');
        }
    }

    public function getPreferences()
    {
        try {
            $userId = $this->request->getAttribute('jwt_user_id');
            $preference = $this->notificationService->getUserPreference($userId, $this->request->query('type'));

            return $this->successResponse($preference);
        } catch (Exception $e) {
            return $this->serverErrorResponse($e->getMessage());
        }
    }
}
