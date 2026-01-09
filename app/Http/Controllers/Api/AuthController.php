<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Contracts\AuthServiceInterface;
use App\Traits\InputValidationTrait;
use Exception;
use OpenApi\Annotations as OA;

/**
 * @OA\Info(
 *     title="Malnu Backend API",
 *     version="1.0.0",
 *     description="RESTful API for Malnu Kananga School Management System",
 *     @OA\Contact(
 *         email="support@malnu.sch.id",
 *         name="Malnu Support"
 *     )
 * )
 * @OA\Server(
 *     url="/api",
 *     description="API Base URL"
 * )
 * @OA\Tag(
 *     name="Authentication",
 *     description="User authentication and authorization endpoints"
 * )
 */
class AuthController extends BaseController
{
    use InputValidationTrait;

    private AuthServiceInterface $authService;

    public function __construct()
    {
        parent::__construct();
        $this->authService = new \App\Services\AuthService();
    }

    /**
     * User registration.
     *
     * @OA\Post(
     *     path="/auth/register",
     *     summary="Register a new user",
     *     tags={"Authentication"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *                 required={"name", "email", "password"},
     *                 @OA\Property(property="name", type="string", example="John Doe", minLength=3),
     *                 @OA\Property(property="email", type="string", format="email", example="john@example.com"),
     *                 @OA\Property(property="password", type="string", format="password", example="SecurePass123", minLength=6)
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Registration successful",
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *                 @OA\Property(property="success", type="boolean", example=true),
     *                 @OA\Property(property="message", type="string", example="User registered successfully"),
     *                 @OA\Property(
     *                     property="data",
     *                     @OA\Property(property="token", type="string", example="eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9..."),
     *                     @OA\Property(
     *                         property="user",
     *                         @OA\Property(property="id", type="string", format="uuid"),
     *                         @OA\Property(property="name", type="string", example="John Doe"),
     *                         @OA\Property(property="email", type="string", format="email", example="john@example.com"),
     *                         @OA\Property(property="role", type="string", example="student")
     *                     )
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Validation error"
     *     )
     * )
     */
    public function register()
    {
        try {
            $data = $this->request->all();

            $data = $this->sanitizeInput($data);

            $requiredFields = ['name', 'email', 'password'];
            $errors = $this->validateRequired($data, $requiredFields);

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

            $result = $this->authService->register($data);

            return $this->successResponse($result, 'User registered successfully');
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage(), 'REGISTRATION_ERROR', null, 400);
        }
    }

    /**
     * User login.
     *
     * @OA\Post(
     *     path="/auth/login",
     *     summary="Authenticate user and get JWT token",
     *     tags={"Authentication"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *                 required={"email", "password"},
     *                 @OA\Property(property="email", type="string", format="email", example="john@example.com"),
     *                 @OA\Property(property="password", type="string", format="password", example="SecurePass123")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Login successful",
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *                 @OA\Property(property="success", type="boolean", example=true),
     *                 @OA\Property(property="message", type="string", example="Login successful"),
     *                 @OA\Property(
     *                     property="data",
     *                     @OA\Property(property="token", type="string", example="eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9..."),
     *                     @OA\Property(
     *                         property="user",
     *                         @OA\Property(property="id", type="string", format="uuid"),
     *                         @OA\Property(property="name", type="string", example="John Doe"),
     *                         @OA\Property(property="email", type="string", format="email", example="john@example.com"),
     *                         @OA\Property(property="role", type="string", example="student")
     *                     ),
     *                     @OA\Property(property="expires_in", type="integer", example=3600)
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Invalid credentials"
     *     )
     * )
     */
    public function login()
    {
        try {
            $data = $this->request->all();

            $data = $this->sanitizeInput($data);

            $requiredFields = ['email', 'password'];
            $errors = $this->validateRequired($data, $requiredFields);

            if (isset($data['email']) && ! $this->validateEmail($data['email'])) {
                $errors['email'] = ['The email must be a valid email address.'];
            }

            if (! empty($errors)) {
                return $this->validationErrorResponse($errors);
            }

            $result = $this->authService->login($data['email'], $data['password']);

            return $this->successResponse($result, 'Login successful');
        } catch (Exception $e) {
            return $this->unauthorizedResponse($e->getMessage());
        }
    }

    /**
     * User logout.
     *
     * @OA\Post(
     *     path="/auth/logout",
     *     summary="Logout user and invalidate JWT token",
     *     tags={"Authentication"},
     *     security={{"BearerAuth": {}}},
     *     @OA\Response(
     *         response=200,
     *         description="Logout successful",
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *                 @OA\Property(property="success", type="boolean", example=true),
     *                 @OA\Property(property="message", type="string", example="Successfully logged out")
     *             )
     *         )
     *     )
     * )
     * )
     * @OA\SecurityScheme(
     *     securityScheme="BearerAuth",
     *     type="http",
     *     scheme="bearer",
     *     bearerFormat="JWT",
     *     description="JWT token authentication"
     * )
     */
    public function logout()
    {
        try {
            $authHeader = $this->request->getHeaderLine('Authorization');
            if (! $authHeader || ! str_starts_with($authHeader, 'Bearer ')) {
                return $this->unauthorizedResponse('Token not provided');
            }

            $token = substr($authHeader, 7);

            $this->authService->logout($token);

            return $this->successResponse(null, 'Successfully logged out');
        } catch (Exception $e) {
            return $this->serverErrorResponse($e->getMessage());
        }
    }

    /**
     * Refresh token.
     *
     * @OA\Post(
     *     path="/auth/refresh",
     *     summary="Refresh JWT token",
     *     tags={"Authentication"},
     *     security={{"BearerAuth": {}}},
     *     @OA\Response(
     *         response=200,
     *         description="Token refreshed successfully",
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *                 @OA\Property(property="success", type="boolean", example=true),
     *                 @OA\Property(property="message", type="string", example="Token refreshed successfully"),
     *                 @OA\Property(
     *                     property="data",
     *                     @OA\Property(property="token", type="string", example="eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9..."),
     *                     @OA\Property(property="expires_in", type="integer", example=3600)
     *                 )
     *             )
     *         )
     *     )
     * )
     */
    public function refresh()
    {
        try {
            $authHeader = $this->request->getHeaderLine('Authorization');
            if (! $authHeader || ! str_starts_with($authHeader, 'Bearer ')) {
                return $this->unauthorizedResponse('Token not provided');
            }

            $token = substr($authHeader, 7);

            $result = $this->authService->refreshToken($token);

            return $this->successResponse($result, 'Token refreshed successfully');
        } catch (Exception $e) {
            return $this->unauthorizedResponse($e->getMessage());
        }
    }

    /**
     * Get authenticated user.
     *
     * @OA\Get(
     *     path="/auth/me",
     *     summary="Get current authenticated user",
     *     tags={"Authentication"},
     *     security={{"BearerAuth": {}}},
     *     @OA\Response(
     *         response=200,
     *         description="User retrieved successfully",
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *                 @OA\Property(property="success", type="boolean", example=true),
     *                 @OA\Property(property="message", type="string", example="User retrieved successfully"),
     *                 @OA\Property(
     *                     property="data",
     *                     @OA\Property(
     *                         property="user",
     *                         @OA\Property(property="id", type="string", format="uuid"),
     *                         @OA\Property(property="name", type="string", example="John Doe"),
     *                         @OA\Property(property="email", type="string", format="email", example="john@example.com"),
     *                         @OA\Property(property="role", type="string", example="student")
     *                     )
     *                 )
     *             )
     *         )
     *     )
     * )
     */
    public function me()
    {
        try {
            $authHeader = $this->request->getHeaderLine('Authorization');
            if (! $authHeader || ! str_starts_with($authHeader, 'Bearer ')) {
                return $this->unauthorizedResponse('Token not provided');
            }

            $token = substr($authHeader, 7);

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
     *
     * @OA\Post(
     *     path="/auth/password/forgot",
     *     summary="Request password reset link",
     *     tags={"Authentication"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *                 required={"email"},
     *                 @OA\Property(property="email", type="string", format="email", example="john@example.com")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Password reset email sent",
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *                 @OA\Property(property="success", type="boolean", example=true),
     *                 @OA\Property(property="message", type="string", example="Password reset email sent")
     *             )
     *         )
     *     )
     * )
     */
    public function requestPasswordReset()
    {
        try {
            $data = $this->request->all();

            $data = $this->sanitizeInput($data);

            $errors = $this->validateRequired($data, ['email']);

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
     *
     * @OA\Post(
     *     path="/auth/password/reset",
     *     summary="Reset password using reset token",
     *     tags={"Authentication"},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *                 required={"token", "password"},
     *                 @OA\Property(property="token", type="string", example="reset-token-here"),
     *                 @OA\Property(property="password", type="string", format="password", example="NewSecurePass123", minLength=6)
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Password reset successful",
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *                 @OA\Property(property="success", type="boolean", example=true),
     *                 @OA\Property(property="message", type="string", example="Password reset successful")
     *             )
     *         )
     *     )
     * )
     */
    public function resetPassword()
    {
        try {
            $data = $this->request->all();

            $data = $this->sanitizeInput($data);

            $requiredFields = ['token', 'password'];
            $errors = $this->validateRequired($data, $requiredFields);

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
     *
     * @OA\Post(
     *     path="/auth/password/change",
     *     summary="Change password for authenticated user",
     *     tags={"Authentication"},
     *     security={{"BearerAuth": {}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *                 required={"current_password", "new_password"},
     *                 @OA\Property(property="current_password", type="string", format="password", example="OldSecurePass123"),
     *                 @OA\Property(property="new_password", type="string", format="password", example="NewSecurePass123", minLength=6)
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Password changed successfully",
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *                 @OA\Property(property="success", type="boolean", example=true),
     *                 @OA\Property(property="message", type="string", example="Password changed successfully")
     *             )
     *         )
     *     )
     * )
     */
    public function changePassword()
    {
        try {
            $data = $this->request->all();

            $data = $this->sanitizeInput($data);

            $requiredFields = ['current_password', 'new_password'];
            $errors = $this->validateRequired($data, $requiredFields);

            if (isset($data['new_password']) && ! $this->validateStringLength($data['new_password'], 6)) {
                $errors['new_password'] = ['The new password must be at least 6 characters.'];
            }

            if (! empty($errors)) {
                return $this->validationErrorResponse($errors);
            }

            $authHeader = $this->request->getHeaderLine('Authorization');
            if (! $authHeader || ! str_starts_with($authHeader, 'Bearer ')) {
                return $this->unauthorizedResponse('Token not provided');
            }

            $token = substr($authHeader, 7);
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
