<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Contracts\AuthServiceInterface;
use App\Traits\InputValidationTrait;

/**
 * @OA\Tag(
 *     name="Password Change",
 *     description="Password change endpoints for authenticated users"
 * )
 */
class PasswordChangeController extends BaseController
{
    use InputValidationTrait;

    private AuthServiceInterface $authService;

    public function __construct(AuthServiceInterface $authService)
    {
        parent::__construct();
        $this->authService = $authService;
    }

    /**
     * Change authenticated user's password
     *
     * @OA\Post(
     *     path="/auth/password/change",
     *     tags={"Password Change"},
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
    public function changePassword()
    {
        try {
            $data = $this->request->all();

            // Sanitize input data
            $data = $this->sanitizeInput($data);

            // Validate required fields
            $requiredFields = ['current_password', 'new_password'];
            $errors = $this->validateRequired($data, $requiredFields);

            // Password complexity validation
            if (isset($data['new_password'])) {
                $passwordErrors = $this->validatePasswordComplexity($data['new_password']);
                if (!empty($passwordErrors)) {
                    $errors['new_password'] = $passwordErrors;
                }
            }

            if (!empty($errors)) {
                return $this->validationErrorResponse($errors);
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
