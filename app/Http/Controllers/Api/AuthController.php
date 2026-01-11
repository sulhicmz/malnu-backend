<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Contracts\AuthServiceInterface;
use App\Traits\InputValidationTrait;
use Exception;
use Hyperf\Context\ApplicationContext;
use Hyperf\HttpServer\Request;
use Hyperf\HttpServer\Response;

class AuthController extends BaseController
{
    use InputValidationTrait;

    private AuthServiceInterface $authService;

    public function __construct(
        AuthServiceInterface $authService
    ) {
        $request = ApplicationContext::getContainer()->get(Request::class);
        $response = ApplicationContext::getContainer()->get(Response::class);
        $container = ApplicationContext::getContainer();
        parent::__construct($request, $response, $container);
        $this->authService = $authService;
    }

    /**
     * User registration.
     */
    public function register()
    {
        try {
            $data = $this->request->all();

            // Sanitize input data
            $data = $this->sanitizeInput($data);

            // Validate required fields
            $requiredFields = ['name', 'email', 'password'];
            $errors = $this->validateRequired($data, $requiredFields);

            // Additional validation
            if (isset($data['email']) && ! $this->validateEmail($data['email'])) {
                $errors['email'] = ['The email must be a valid email address.'];
            }

            if (isset($data['name']) && ! $this->validateStringLength($data['name'], 3)) {
                $errors['name'] = ['The name must be at least 3 characters.'];
            }

            if (isset($data['password']) && ! $this->validateStringLength($data['password'], 6)) {
                $errors['password'] = ['The password must be at least 6 characters.'];
            }

            if (! empty($errors)) {
                return $this->validationErrorResponse($errors);
            }

            // Register user
            $result = $this->authService->register($data);

            return $this->successResponse($result, 'User registered successfully');
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage(), config('error-codes.error_codes.RESOURCE.CREATION_FAILED', 'RES_002'), null, 400);
        }
    }

    /**
     * User login.
     */
    public function login()
    {
        try {
            $data = $this->request->all();

            // Sanitize input data
            $data = $this->sanitizeInput($data);

            // Validate required fields
            $requiredFields = ['email', 'password'];
            $errors = $this->validateRequired($data, $requiredFields);

            // Additional validation
            if (isset($data['email']) && ! $this->validateEmail($data['email'])) {
                $errors['email'] = ['The email must be a valid email address.'];
            }

            if (! empty($errors)) {
                return $this->validationErrorResponse($errors);
            }

            // Authenticate user
            $result = $this->authService->login($data['email'], $data['password']);

            return $this->successResponse($result, 'Login successful');
        } catch (Exception $e) {
            return $this->unauthorizedResponse($e->getMessage());
        }
    }

    /**
     * User logout.
     */
    public function logout()
    {
        try {
            // Get token from authorization header
            $authHeader = $this->request->getHeaderLine('Authorization');
            if (! $authHeader || ! str_starts_with($authHeader, 'Bearer ')) {
                return $this->unauthorizedResponse('Token not provided');
            }

            $token = substr($authHeader, 7); // Remove 'Bearer ' prefix

            // Add token to blacklist
            $this->authService->logout($token);

            return $this->successResponse(null, 'Successfully logged out');
        } catch (Exception $e) {
            return $this->serverErrorResponse($e->getMessage());
        }
    }

    /**
     * Refresh token.
     */
    public function refresh()
    {
        try {
            // Get token from authorization header
            $authHeader = $this->request->getHeaderLine('Authorization');
            if (! $authHeader || ! str_starts_with($authHeader, 'Bearer ')) {
                return $this->unauthorizedResponse('Token not provided');
            }

            $token = substr($authHeader, 7); // Remove 'Bearer ' prefix

            $result = $this->authService->refreshToken($token);

            return $this->successResponse($result, 'Token refreshed successfully');
        } catch (Exception $e) {
            return $this->unauthorizedResponse($e->getMessage());
        }
    }

    /**
     * Get authenticated user.
     */
    public function me()
    {
        try {
            // Get token from authorization header
            $authHeader = $this->request->getHeaderLine('Authorization');
            if (! $authHeader || ! str_starts_with($authHeader, 'Bearer ')) {
                return $this->unauthorizedResponse('Token not provided');
            }

            $token = substr($authHeader, 7); // Remove 'Bearer ' prefix

            $user = $this->authService->getUserFromToken($token);

            if (! $user) {
                return $this->unauthorizedResponse('User not authenticated');
            }

            return $this->successResponse([
                'user' => $user,
            ], 'User retrieved successfully');
        } catch (Exception $e) {
            return $this->unauthorizedResponse($e->getMessage());
        }
    }

    /**
     * Request password reset.
     */
    public function requestPasswordReset()
    {
        try {
            $data = $this->request->all();

            // Sanitize input data
            $data = $this->sanitizeInput($data);

            // Validate required fields
            $errors = $this->validateRequired($data, ['email']);

            // Additional validation
            if (isset($data['email']) && ! $this->validateEmail($data['email'])) {
                $errors['email'] = ['The email must be a valid email address.'];
            }

            if (! empty($errors)) {
                return $this->validationErrorResponse($errors);
            }

            $result = $this->authService->requestPasswordReset($data['email']);

            return $this->successResponse($result, $result['message']);
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage());
        }
    }

    /**
     * Reset password with token.
     */
    public function resetPassword()
    {
        try {
            $data = $this->request->all();

            // Sanitize input data
            $data = $this->sanitizeInput($data);

            // Validate required fields
            $requiredFields = ['token', 'password'];
            $errors = $this->validateRequired($data, $requiredFields);

            // Additional validation
            if (isset($data['password']) && ! $this->validateStringLength($data['password'], 6)) {
                $errors['password'] = ['The password must be at least 6 characters.'];
            }

            if (! empty($errors)) {
                return $this->validationErrorResponse($errors);
            }

            $result = $this->authService->resetPassword($data['token'], $data['password']);

            return $this->successResponse($result, $result['message']);
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage());
        }
    }

    /**
     * Change authenticated user's password.
     */
    public function changePassword()
    {
        try {
            $data = $this->request->all();

            // Sanitize input data
            $data = $this->sanitizeInput($data);

            // Validate required fields
            $requiredFields = ['current_password', 'new_password'];
            $errors = $this->validateRequired($data, $requiredFields);

            // Additional validation
            if (isset($data['new_password']) && ! $this->validateStringLength($data['new_password'], 6)) {
                $errors['new_password'] = ['The new password must be at least 6 characters.'];
            }

            if (! empty($errors)) {
                return $this->validationErrorResponse($errors);
            }

            // Get user from token
            $authHeader = $this->request->getHeaderLine('Authorization');
            if (! $authHeader || ! str_starts_with($authHeader, 'Bearer ')) {
                return $this->unauthorizedResponse('Token not provided');
            }

            $token = substr($authHeader, 7); // Remove 'Bearer ' prefix
            $user = $this->authService->getUserFromToken($token);

            if (! $user) {
                return $this->unauthorizedResponse('User not authenticated');
            }

            $result = $this->authService->changePassword($user['id'], $data['current_password'], $data['new_password']);

            return $this->successResponse($result, $result['message']);
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage());
        }
    }
}
