<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Contracts\AuthServiceInterface;
use App\Traits\InputValidationTrait;

/**
 * @OA\Tag(
 *     name="Password Reset",
 *     description="Password reset flow endpoints"
 * )
 */
class PasswordResetController extends BaseController
{
    use InputValidationTrait;

    private AuthServiceInterface $authService;

    public function __construct(AuthServiceInterface $authService)
    {
        parent::__construct();
        $this->authService = $authService;
    }

    /**
     * Request password reset
     *
     * @OA\Post(
     *     path="/auth/password/forgot",
     *     tags={"Password Reset"},
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
    public function requestPasswordReset()
    {
        try {
            $data = $this->request->all();

            // Sanitize input data
            $data = $this->sanitizeInput($data);

            // Validate required fields
            $errors = $this->validateRequired($data, ['email']);

            // Additional validation
            if (isset($data['email']) && !$this->validateEmail($data['email'])) {
                $errors['email'] = ['The email must be a valid email address.'];
            }

            if (!empty($errors)) {
                return $this->validationErrorResponse($errors);
            }

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
     *     path="/auth/password/reset",
     *     tags={"Password Reset"},
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
    public function resetPassword()
    {
        try {
            $data = $this->request->all();

            // Sanitize input data
            $data = $this->sanitizeInput($data);

            // Validate required fields
            $requiredFields = ['token', 'password'];
            $errors = $this->validateRequired($data, $requiredFields);

            // Password complexity validation
            if (isset($data['password'])) {
                $passwordErrors = $this->validatePasswordComplexity($data['password']);
                if (!empty($passwordErrors)) {
                    $errors['password'] = $passwordErrors;
                }
            }

            if (!empty($errors)) {
                return $this->validationErrorResponse($errors);
            }

            $result = $this->authService->resetPassword($data['token'], $data['password']);

            return $this->successResponse($result, $result['message']);
        } catch (\Exception $e) {
            return $this->errorResponse($e->getMessage());
        }
    }
}
