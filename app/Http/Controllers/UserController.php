<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Services\UserService;
use App\Services\QueryOptimizationService;
use Hypervel\Http\Request;
use Hypervel\Http\JsonResponse;

class UserController extends AbstractController
{
    private UserService $userService;
    private QueryOptimizationService $queryOptimizationService;

    public function __construct(
        UserService $userService,
        QueryOptimizationService $queryOptimizationService
    ) {
        $this->userService = $userService;
        $this->queryOptimizationService = $queryOptimizationService;
    }

    /**
     * Get all users with optimized queries and caching
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $perPage = (int) $request->get('per_page', 15);
            $perPage = min(100, max(1, $perPage)); // Limit between 1 and 100

            if ($request->get('paginate', false)) {
                $users = $this->userService->getUsersPaginated($perPage);
            } else {
                $users = $this->userService->getAllUsers();
            }

            return new JsonResponse([
                'success' => true,
                'data' => $users,
                'message' => 'Users retrieved successfully'
            ]);
        } catch (\Exception $e) {
            return new JsonResponse([
                'success' => false,
                'message' => 'Error retrieving users: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get a specific user by ID with caching
     */
    public function show(Request $request, string $id): JsonResponse
    {
        try {
            $user = $this->userService->getUserById($id);

            if (!$user) {
                return new JsonResponse([
                    'success' => false,
                    'message' => 'User not found'
                ], 404);
            }

            return new JsonResponse([
                'success' => true,
                'data' => $user,
                'message' => 'User retrieved successfully'
            ]);
        } catch (\Exception $e) {
            return new JsonResponse([
                'success' => false,
                'message' => 'Error retrieving user: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get user by email with caching
     */
    public function showByEmail(Request $request, string $email): JsonResponse
    {
        try {
            $user = $this->userService->getUserByEmail($email);

            if (!$user) {
                return new JsonResponse([
                    'success' => false,
                    'message' => 'User not found'
                ], 404);
            }

            return new JsonResponse([
                'success' => true,
                'data' => $user,
                'message' => 'User retrieved successfully'
            ]);
        } catch (\Exception $e) {
            return new JsonResponse([
                'success' => false,
                'message' => 'Error retrieving user: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get users with roles using optimized queries
     */
    public function getUsersWithRoles(Request $request): JsonResponse
    {
        try {
            $users = $this->queryOptimizationService->getUsersWithRolesOptimized();

            return new JsonResponse([
                'success' => true,
                'data' => $users,
                'message' => 'Users with roles retrieved successfully'
            ]);
        } catch (\Exception $e) {
            return new JsonResponse([
                'success' => false,
                'message' => 'Error retrieving users with roles: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get paginated users with relationships optimized
     */
    public function getPaginatedUsersWithRelationships(Request $request): JsonResponse
    {
        try {
            $perPage = (int) $request->get('per_page', 15);
            $perPage = min(100, max(1, $perPage)); // Limit between 1 and 100

            $users = $this->queryOptimizationService->getPaginatedUsersWithRelationships($perPage);

            return new JsonResponse([
                'success' => true,
                'data' => $users,
                'message' => 'Paginated users with relationships retrieved successfully'
            ]);
        } catch (\Exception $e) {
            return new JsonResponse([
                'success' => false,
                'message' => 'Error retrieving paginated users: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get users with specific columns for optimization
     */
    public function getUsersWithSpecificColumns(Request $request): JsonResponse
    {
        try {
            $users = $this->queryOptimizationService->getUsersWithSpecificColumns();

            return new JsonResponse([
                'success' => true,
                'data' => $users,
                'message' => 'Users with specific columns retrieved successfully'
            ]);
        } catch (\Exception $e) {
            return new JsonResponse([
                'success' => false,
                'message' => 'Error retrieving users: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get users with role condition
     */
    public function getUsersWithRoleCondition(Request $request, string $roleName): JsonResponse
    {
        try {
            $users = $this->queryOptimizationService->getUsersWithRoleCondition($roleName);

            return new JsonResponse([
                'success' => true,
                'data' => $users,
                'message' => 'Users with role condition retrieved successfully'
            ]);
        } catch (\Exception $e) {
            return new JsonResponse([
                'success' => false,
                'message' => 'Error retrieving users: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Clear user-related caches
     */
    public function clearCache(Request $request): JsonResponse
    {
        try {
            $this->queryOptimizationService->clearOptimizationCaches();
            
            return new JsonResponse([
                'success' => true,
                'message' => 'User-related caches cleared successfully'
            ]);
        } catch (\Exception $e) {
            return new JsonResponse([
                'success' => false,
                'message' => 'Error clearing caches: ' . $e->getMessage()
            ], 500);
        }
    }
}