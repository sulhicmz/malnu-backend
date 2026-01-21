<?php
 
declare(strict_types=1);
 
namespace App\Http\Controllers\Api;
 
use App\Contracts\AuthServiceInterface;
use App\Http\Requests\Auth\ChangePasswordRequest;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\RegisterRequest;
use App\Http\Requests\Auth\RequestPasswordResetRequest;
use App\Http\Requests\Auth\ResetPasswordRequest;

/**
 * @OA\Info(
 *     title="Malnu Backend API",
 *     version="1.0.0",
 *     description="API endpoints for Malnu School Management System"
 * )
 * @OA\Tag(
 *     name="Authentication",
 *     description="User authentication and authorization endpoints"
 * )
 * @OA\Server(
 *     url="http://localhost:9501",
 *     description="Local development server"
 * )
 * @OA\Server(
 *     url="https://api.example.com",
 *     description="Production server"
 * )
 * @OA\SecurityScheme(
 *     securityScheme="bearerAuth",
 *     type="http",
 *     scheme="bearer",
 *     bearerFormat="JWT"
 * )
 */
class AuthController extends BaseController
{
    use InputValidationTrait;
    
    private AuthServiceInterface $authService;

    public function __construct(AuthServiceInterface $authService)
    {
        parent::__construct();
        $this->authService = $authService;
    }

    /**
     * User registration
     * 
     * @OA\Post(
     *     path="/auth/register",
     *     tags={"Authentication"},
     *     summary="Register a new user",
     *     description="Create a new user account with email and password",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"name","email","password"},
     *             @OA\Property(property="name", type="string", example="John Doe"),
     *             @OA\Property(property="email", type="string", format="email", example="john@example.com"),
     *             @OA\Property(property="password", type="string", format="password", example="SecurePass123!", minLength=8)
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="User registered successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="User registered successfully"),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="token", type="string", example="eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9..."),
     *                 @OA\Property(property="user", type="object",
     *                     @OA\Property(property="id", type="string", format="uuid"),
     *                     @OA\Property(property="name", type="string", example="John Doe"),
     *                     @OA\Property(property="email", type="string", format="email", example="john@example.com"),
     *                     @OA\Property(property="role", type="string", example="student")
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Validation error or registration failed",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Validation error"),
     *             @OA\Property(property="errors", type="object")
     *         )
     *     )
     * )
     */
    public function register(RegisterRequest $request)
    {
        try {
            $data = $request->validated();

            // Register user
            $result = $this->authService->register($data);

            return $this->successResponse($result, 'User registered successfully');
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage(), 'REGISTRATION_ERROR', null, 400);
        }
    }

    /**
     * User login
     * 
     * @OA\Post(
     *     path="/auth/login",
     *     tags={"Authentication"},
     *     summary="User login",
     *     description="Authenticate user with email and password",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"email","password"},
     *             @OA\Property(property="email", type="string", format="email", example="john@example.com"),
     *             @OA\Property(property="password", type="string", format="password", example="SecurePass123!")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Login successful",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Login successful"),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="token", type="string", example="eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9..."),
     *                 @OA\Property(property="user", type="object")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized - invalid credentials",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Invalid credentials")
     *         )
     *     )
     * )
     */
    public function login(LoginRequest $request)
    {
        try {
            $data = $request->validated();

            // Authenticate user
            $result = $this->authService->login($data['email'], $data['password']);

            return $this->successResponse($result, 'Login successful');
        } catch (\Exception $e) {
            return $this->unauthorizedResponse($e->getMessage());
        }
    }

    /**
     * User logout
     * 
     * @OA\Post(
     *     path="/auth/logout",
     *     tags={"Authentication"},
     *     summary="User logout",
     *     description="Logout authenticated user and invalidate token",
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Logout successful",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Successfully logged out")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized - token not provided or invalid",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Token not provided")
     *         )
     *     )
     * )
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
     * 
     * @OA\Post(
     *     path="/auth/refresh",
     *     tags={"Authentication"},
     *     summary="Refresh JWT token",
     *     description="Refresh expired JWT token",
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="Token refreshed successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Token refreshed successfully"),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="token", type="string", example="eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9...")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Invalid token")
     *         )
     *     )
     * )
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
     * 
     * @OA\Get(
     *     path="/auth/me",
     *     tags={"Authentication"},
     *     summary="Get authenticated user",
     *     description="Retrieve current authenticated user information",
     *     security={{"bearerAuth":{}}},
     *     @OA\Response(
     *         response=200,
     *         description="User retrieved successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="User retrieved successfully"),
     *             @OA\Property(property="data", type="object",
     *                 @OA\Property(property="user", type="object")
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="User not authenticated")
     *         )
     *     )
     * )
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
     * 
     * @OA\Post(
     *     path="/auth/password-reset/request",
     *     tags={"Authentication"},
     *     summary="Request password reset",
     *     description="Send password reset link to user email",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"email"},
     *             @OA\Property(property="email", type="string", format="email", example="john@example.com")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Password reset email sent",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Password reset link sent to your email"),
     *             @OA\Property(property="data", type="object")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Validation error"),
     *             @OA\Property(property="errors", type="object")
     *         )
     *     )
     * )
     */
    public function requestPasswordReset(RequestPasswordResetRequest $request)
    {
        try {
            $data = $request->validated();

            $result = $this->authService->requestPasswordReset($data['email']);

            return $this->successResponse($result, $result['message']);
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage());
        }
    }

    /**
     * Reset password with token
     * 
     * @OA\Post(
     *     path="/auth/password-reset/confirm",
     *     tags={"Authentication"},
     *     summary="Reset password",
     *     description="Reset user password using reset token",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"token","password"},
     *             @OA\Property(property="token", type="string", example="abc123def456"),
     *             @OA\Property(property="password", type="string", format="password", example="NewSecurePass123!", minLength=8)
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Password reset successful",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Password has been reset"),
     *             @OA\Property(property="data", type="object")
     *         )
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Invalid or expired token",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Invalid or expired token")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Validation error"),
     *             @OA\Property(property="errors", type="object")
     *         )
     *     )
     * )
     */
    public function resetPassword(ResetPasswordRequest $request)
    {
        try {
            $data = $request->validated();

            $result = $this->authService->resetPassword($data['token'], $data['password']);

            return $this->successResponse($result, $result['message']);
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage());
        }
    }

    /**
     * Change authenticated user's password
     * 
     * @OA\Post(
     *     path="/auth/change-password",
     *     tags={"Authentication"},
     *     summary="Change password",
     *     description="Change authenticated user's password",
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"current_password","new_password"},
     *             @OA\Property(property="current_password", type="string", format="password", example="OldPass123!"),
     *             @OA\Property(property="new_password", type="string", format="password", example="NewSecurePass123!", minLength=8)
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Password changed successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="message", type="string", example="Password has been changed"),
     *             @OA\Property(property="data", type="object")
     *         )
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Current password is incorrect")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Validation error"),
     *             @OA\Property(property="errors", type="object")
     *         )
     *     )
     * )
     */
    public function changePassword(ChangePasswordRequest $request)
    {
        try {
            $data = $request->validated();

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