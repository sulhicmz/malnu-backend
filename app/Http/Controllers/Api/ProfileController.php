<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Contracts\AuthServiceInterface;
use App\Traits\InputValidationTrait;

/**
 * @OA\Tag(
 *     name="Profile",
 *     description="User profile management endpoints"
 * )
 */
class ProfileController extends BaseController
{
    use InputValidationTrait;

    private AuthServiceInterface $authService;

    public function __construct(AuthServiceInterface $authService)
    {
        parent::__construct();
        $this->authService = $authService;
    }

    /**
     * Get authenticated user
     *
     * @OA\Get(
     *     path="/auth/me",
     *     tags={"Profile"},
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
}
