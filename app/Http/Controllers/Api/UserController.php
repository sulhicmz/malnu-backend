<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\AbstractController;
use App\Services\UserService;
use Hypervel\Http\Request;
use Hypervel\Http\Response;

class UserController extends AbstractController
{
    private UserService $userService;

    public function __construct(UserService $userService)
    {
        $this->userService = $userService;
    }

    /**
     * Get all users with optimized queries
     */
    public function index(Request $request): Response
    {
        // Use cached service to get all users with relationships
        $users = $this->userService->getAllUsers();
        
        return $this->response->json([
            'data' => $users,
            'count' => $users->count(),
        ]);
    }

    /**
     * Get a specific user with optimized query
     */
    public function show(Request $request, string $id): Response
    {
        $user = $this->userService->getUserById($id);
        
        if (!$user) {
            return $this->response->json([
                'error' => 'User not found',
            ], 404);
        }
        
        return $this->response->json([
            'data' => $user,
        ]);
    }

    /**
     * Get users count with caching
     */
    public function count(Request $request): Response
    {
        $count = $this->userService->getUsersCount();
        
        return $this->response->json([
            'count' => $count,
        ]);
    }

    /**
     * Create a new user
     */
    public function store(Request $request): Response
    {
        $data = $request->all();
        
        // Validate required fields
        if (empty($data['email']) || empty($data['name'])) {
            return $this->response->json([
                'error' => 'Email and name are required',
            ], 422);
        }
        
        $user = $this->userService->createUser($data);
        
        return $this->response->json([
            'data' => $user,
        ], 201);
    }

    /**
     * Update a user
     */
    public function update(Request $request, string $id): Response
    {
        $data = $request->all();
        
        $user = $this->userService->updateUser($id, $data);
        
        if (!$user) {
            return $this->response->json([
                'error' => 'User not found',
            ], 404);
        }
        
        return $this->response->json([
            'data' => $user,
        ]);
    }

    /**
     * Delete a user
     */
    public function destroy(Request $request, string $id): Response
    {
        $result = $this->userService->deleteUser($id);
        
        if (!$result) {
            return $this->response->json([
                'error' => 'User not found',
            ], 404);
        }
        
        return $this->response->json([
            'message' => 'User deleted successfully',
        ]);
    }
}