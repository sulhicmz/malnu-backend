<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Services\UserService;
use Hypervel\Http\Request;

class UserController extends AbstractController
{
    protected UserService $userService;

    public function __construct(UserService $userService)
    {
        $this->userService = $userService;
    }

    public function index(Request $request)
    {
        // Using cached service to get all users with eager loaded relationships
        $users = $this->userService->getAllUsers();
        
        return response()->json([
            'data' => $users,
            'count' => $users->count(),
        ]);
    }

    public function show(Request $request, string $id)
    {
        // Using cached service to get a specific user
        $user = $this->userService->getUserById($id);
        
        if (!$user) {
            return response()->json(['error' => 'User not found'], 404);
        }
        
        return response()->json([
            'data' => $user,
        ]);
    }

    public function byRole(Request $request, string $role)
    {
        // Using cached service to get users by role
        $users = $this->userService->getUsersByRole($role);
        
        return response()->json([
            'data' => $users,
            'count' => $users->count(),
        ]);
    }
}