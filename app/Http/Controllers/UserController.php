<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Services\UserService;
use Hypervel\Http\Request;
use Hypervel\Http\JsonResponse;

class UserController extends AbstractController
{
    private UserService $userService;

    public function __construct(UserService $userService)
    {
        $this->userService = $userService;
    }

    /**
     * Get all users with optimized query and caching
     */
    public function index(Request $request): JsonResponse
    {
        $users = $this->userService->getAllUsers();
        
        return new JsonResponse([
            'data' => $users,
            'count' => $users->count(),
        ]);
    }

    /**
     * Get a specific user by ID with caching
     */
    public function show(string $id): JsonResponse
    {
        $user = $this->userService->getUserById($id);
        
        if (!$user) {
            return new JsonResponse(['error' => 'User not found'], 404);
        }
        
        return new JsonResponse([
            'data' => $user,
        ]);
    }

    /**
     * Get active users with caching
     */
    public function active(): JsonResponse
    {
        $users = $this->userService->getActiveUsers();
        
        return new JsonResponse([
            'data' => $users,
            'count' => $users->count(),
        ]);
    }

    /**
     * Create a new user
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'username' => 'required|string|unique:users,username',
            'password' => 'required|string|min:8',
        ]);

        $validated['password'] = password_hash($validated['password'], PASSWORD_DEFAULT);
        
        $user = $this->userService->createUser($validated);
        
        return new JsonResponse([
            'data' => $user,
            'message' => 'User created successfully',
        ], 201);
    }

    /**
     * Update an existing user
     */
    public function update(Request $request, string $id): JsonResponse
    {
        $user = $this->userService->getUserById($id);
        
        if (!$user) {
            return new JsonResponse(['error' => 'User not found'], 404);
        }

        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'email' => 'sometimes|email|unique:users,email,' . $id,
            'username' => 'sometimes|string|unique:users,username,' . $id,
            'password' => 'sometimes|string|min:8',
            'is_active' => 'sometimes|boolean',
        ]);

        if (isset($validated['password'])) {
            $validated['password'] = password_hash($validated['password'], PASSWORD_DEFAULT);
        }
        
        $user = $this->userService->updateUser($user, $validated);
        
        return new JsonResponse([
            'data' => $user,
            'message' => 'User updated successfully',
        ]);
    }

    /**
     * Delete a user
     */
    public function destroy(string $id): JsonResponse
    {
        $user = $this->userService->getUserById($id);
        
        if (!$user) {
            return new JsonResponse(['error' => 'User not found'], 404);
        }

        $this->userService->deleteUser($user);
        
        return new JsonResponse([
            'message' => 'User deleted successfully',
        ]);
    }
}