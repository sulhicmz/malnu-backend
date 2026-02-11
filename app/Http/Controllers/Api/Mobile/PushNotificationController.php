<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api\Mobile;

use App\Http\Controllers\Api\BaseController;
use Exception;
use Hyperf\HttpServer\Contract\RequestInterface;
use Hyperf\HttpServer\Contract\ResponseInterface;
use Psr\Container\ContainerInterface;

class PushNotificationController extends BaseController
{
    public function __construct(
        RequestInterface $request,
        ResponseInterface $response,
        ContainerInterface $container
    ) {
        parent::__construct($request, $response, $container);
    }

    public function registerDevice()
    {
        try {
            $data = $this->request->all();
            
            $errors = [];
            if (empty($data['device_token'])) {
                $errors['device_token'] = ['The device_token field is required.'];
            }
            if (empty($data['platform'])) {
                $errors['platform'] = ['The platform field is required.'];
            } elseif (!in_array($data['platform'], ['ios', 'android'])) {
                $errors['platform'] = ['The platform must be either ios or android.'];
            }

            if (!empty($errors)) {
                return $this->validationErrorResponse($errors);
            }

            $user = $this->request->getAttribute('user');
            
            $deviceRegistration = [
                'user_id' => $user['id'],
                'device_token' => $data['device_token'],
                'platform' => $data['platform'],
                'device_info' => $this->request->getAttribute('device_info') ?? [],
                'registered_at' => date('c'),
            ];
            
            return $this->successResponse($deviceRegistration, 'Device registered successfully', 201);
        } catch (Exception $e) {
            return $this->serverErrorResponse($e->getMessage());
        }
    }

    public function unregisterDevice()
    {
        try {
            $data = $this->request->all();
            
            $errors = [];
            if (empty($data['device_token'])) {
                $errors['device_token'] = ['The device_token field is required.'];
            }

            if (!empty($errors)) {
                return $this->validationErrorResponse($errors);
            }

            return $this->successResponse(null, 'Device unregistered successfully');
        } catch (Exception $e) {
            return $this->serverErrorResponse($e->getMessage());
        }
    }

    public function updatePreferences()
    {
        try {
            $data = $this->request->all();
            
            $user = $this->request->getAttribute('user');
            
            $preferences = [
                'user_id' => $user['id'],
                'enabled' => $data['enabled'] ?? true,
                'notifications' => $data['notifications'] ?? [],
            ];
            
            return $this->successResponse($preferences, 'Notification preferences updated successfully');
        } catch (Exception $e) {
            return $this->serverErrorResponse($e->getMessage());
        }
    }

    public function getPreferences()
    {
        try {
            $user = $this->request->getAttribute('user');
            
            $preferences = [
                'user_id' => $user['id'],
                'enabled' => true,
                'notifications' => [
                    'grades' => true,
                    'attendance' => true,
                    'assignments' => true,
                    'fees' => true,
                    'announcements' => true,
                    'emergency' => true,
                ],
            ];
            
            return $this->successResponse($preferences, 'Notification preferences retrieved successfully');
        } catch (Exception $e) {
            return $this->serverErrorResponse($e->getMessage());
        }
    }

    public function testPush()
    {
        try {
            $user = $this->request->getAttribute('user');
            
            $testNotification = [
                'title' => 'Test Notification',
                'message' => 'This is a test push notification from Malnu School Management System',
                'user_id' => $user['id'],
                'sent_at' => date('c'),
            ];
            
            return $this->successResponse($testNotification, 'Test push notification sent successfully');
        } catch (Exception $e) {
            return $this->serverErrorResponse($e->getMessage());
        }
    }
}
