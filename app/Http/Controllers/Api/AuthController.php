<?php

namespace App\Http\Controllers\Api;

use App\Services\AuthService;

class AuthController extends BaseController
{
    private AuthService $authService;

    public function __construct()
    {
        parent::__construct();
        $this->authService = new AuthService();
    }

    /**
     * User registration
     */
    public function register()
    {
        try {
            $data = $this->request->all();
            
            // Validate required fields
            $requiredFields = ['name', 'email', 'password'];
            foreach ($requiredFields as $field) {
                if (empty($data[$field])) {
                    return $this->validationErrorResponse([
                        $field => ["The {$field} field is required."]
                    ]);
                }
            }

            // Register user
            $result = $this->authService->register($data);

            return $this->successResponse($result, 'User registered successfully');
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 'REGISTRATION_ERROR', null, 400);
        }
    }

    /**
     * User login
     */
    public function login()
    {
        try {
            $data = $this->request->all();
            
            // Validate required fields
            $requiredFields = ['email', 'password'];
            foreach ($requiredFields as $field) {
                if (empty($data[$field])) {
                    return $this->validationErrorResponse([
                        $field => ["The {$field} field is required."]
                    ]);
                }
            }

            // Authenticate user
            $result = $this->authService->login($data['email'], $data['password']);

            return $this->successResponse($result, 'Login successful');
        } catch (\Exception $e) {
            return $this->unauthorizedResponse($e->getMessage());
        }
    }

    /**
     * User logout
     */
    public function logout()
    {
        try {
            // Get token from authorization header
            $authHeader = $this->request->getHeaderLine('Authorization');
            if (!$authHeader || !str_starts_with($authHeader, 'Bearer ')) {
                return $this->unauthorizedResponse('Token not provided');
            }
            
            $token = substr($authHeader, 7); // Remove 'Bearer ' prefix
            
            // Add token to blacklist
            $this->authService->logout($token);
            
            return $this->successResponse(null, 'Successfully logged out');
        } catch (\Exception $e) {
            return $this->serverErrorResponse($e->getMessage());
        }
    }

    /**
     * Refresh token
     */
    public function refresh()
    {
        try {
            // Get token from authorization header
            $authHeader = $this->request->getHeaderLine('Authorization');
            if (!$authHeader || !str_starts_with($authHeader, 'Bearer ')) {
                return $this->unauthorizedResponse('Token not provided');
            }
            
            $token = substr($authHeader, 7); // Remove 'Bearer ' prefix
            
            $result = $this->authService->refreshToken($token);
            
            return $this->successResponse($result, 'Token refreshed successfully');
        } catch (\Exception $e) {
            return $this->unauthorizedResponse($e->getMessage());
        }
    }

    /**
     * Get authenticated user
     */
    public function me()
    {
        try {
            // Get token from authorization header
            $authHeader = $this->request->getHeaderLine('Authorization');
            if (!$authHeader || !str_starts_with($authHeader, 'Bearer ')) {
                return $this->unauthorizedResponse('Token not provided');
            }
            
            $token = substr($authHeader, 7); // Remove 'Bearer ' prefix
            
            $user = $this->authService->getUserFromToken($token);
            
            if (!$user) {
                return $this->unauthorizedResponse('User not authenticated');
            }

            return $this->successResponse([
                'user' => $user
            ], 'User retrieved successfully');
        } catch (\Exception $e) {
            return $this->unauthorizedResponse($e->getMessage());
        }
    }

    /**
     * Request password reset
     */
    public function requestPasswordReset()
    {
        try {
            $data = $this->request->all();
            
            if (empty($data['email'])) {
                return $this->validationErrorResponse([
                    'email' => ['The email field is required.']
                ]);
            }

            $result = $this->authService->requestPasswordReset($data['email']);
            
            return $this->successResponse($result, $result['message']);
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage());
        }
    }

    /**
     * Reset password with token
     */
    public function resetPassword()
    {
        try {
            $data = $this->request->all();
            
            $requiredFields = ['token', 'password'];
            foreach ($requiredFields as $field) {
                if (empty($data[$field])) {
                    return $this->validationErrorResponse([
                        $field => ["The {$field} field is required."]
                    ]);
                }
            }

            $result = $this->authService->resetPassword($data['token'], $data['password']);
            
            return $this->successResponse($result, $result['message']);
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage());
        }
    }

    /**
     * Change authenticated user's password
     */
    public function changePassword()
    {
        try {
            $data = $this->request->all();
            
            $requiredFields = ['current_password', 'new_password'];
            foreach ($requiredFields as $field) {
                if (empty($data[$field])) {
                    return $this->validationErrorResponse([
                        $field => ["The {$field} field is required."]
                    ]);
                }
            }

            // Get user from token
            $authHeader = $this->request->getHeaderLine('Authorization');
            if (!$authHeader || !str_starts_with($authHeader, 'Bearer ')) {
                return $this->unauthorizedResponse('Token not provided');
            }
            
            $token = substr($authHeader, 7); // Remove 'Bearer ' prefix
            $user = $this->authService->getUserFromToken($token);
            
            if (!$user) {
                return $this->unauthorizedResponse('User not authenticated');
            }

            $result = $this->authService->changePassword($user['id'], $data['current_password'], $data['new_password']);
            
            return $this->successResponse($result, $result['message']);
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage());
        }
    }
}