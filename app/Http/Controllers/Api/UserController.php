<?php

declare(strict_types=1);

namespace App\Http\Controllers\Api;

use App\Http\Controllers\AbstractController;
use App\Models\User;
use App\Services\UserService;
use Hyperf\HttpServer\Annotation\Controller;
use Hyperf\HttpServer\Annotation\GetMapping;
use Hyperf\HttpServer\Annotation\PostMapping;
use Hyperf\HttpServer\Annotation\PutMapping;
use Hyperf\HttpServer\Annotation\DeleteMapping;
use Hyperf\HttpServer\Annotation\RequestMapping;
use Hyperf\HttpServer\Contract\RequestInterface;
use Hyperf\HttpServer\Contract\ResponseInterface;
use Psr\Http\Message\ResponseInterface as PsrResponseInterface;

/**
 * @Controller(prefix="api/users")
 */
class UserController extends AbstractController
{
    private UserService $userService;

    public function __construct(UserService $userService)
    {
        $this->userService = $userService;
    }

    /**
     * @GetMapping(path="/")
     */
    public function index(ResponseInterface $response): PsrResponseInterface
    {
        try {
            // Using the service with caching
            $users = $this->userService->getAllUsers();
            
            return $response->json([
                'success' => true,
                'data' => $users,
                'count' => $users->count()
            ]);
        } catch (\Exception $e) {
            return $response->json([
                'success' => false,
                'message' => 'Error retrieving users: ' . $e->getMessage()
            ])->withStatus(500);
        }
    }

    /**
     * @GetMapping(path="/{id}")
     */
    public function show(string $id, ResponseInterface $response): PsrResponseInterface
    {
        try {
            $user = $this->userService->getUserById($id);
            
            if (!$user) {
                return $response->json([
                    'success' => false,
                    'message' => 'User not found'
                ])->withStatus(404);
            }
            
            return $response->json([
                'success' => true,
                'data' => $user
            ]);
        } catch (\Exception $e) {
            return $response->json([
                'success' => false,
                'message' => 'Error retrieving user: ' . $e->getMessage()
            ])->withStatus(500);
        }
    }

    /**
     * @GetMapping(path="/email/{email}")
     */
    public function showByEmail(string $email, ResponseInterface $response): PsrResponseInterface
    {
        try {
            $user = $this->userService->getUserByEmail($email);
            
            if (!$user) {
                return $response->json([
                    'success' => false,
                    'message' => 'User not found'
                ])->withStatus(404);
            }
            
            return $response->json([
                'success' => true,
                'data' => $user
            ]);
        } catch (\Exception $e) {
            return $response->json([
                'success' => false,
                'message' => 'Error retrieving user: ' . $e->getMessage()
            ])->withStatus(500);
        }
    }

    /**
     * @PostMapping(path="/")
     */
    public function store(RequestInterface $request, ResponseInterface $response): PsrResponseInterface
    {
        try {
            $data = $request->all();
            
            // Validate required fields
            if (empty($data['name']) || empty($data['email']) || empty($data['password'])) {
                return $response->json([
                    'success' => false,
                    'message' => 'Name, email, and password are required'
                ])->withStatus(400);
            }
            
            // Check if user already exists
            if (User::where('email', $data['email'])->exists()) {
                return $response->json([
                    'success' => false,
                    'message' => 'User with this email already exists'
                ])->withStatus(409);
            }
            
            // Hash password
            $data['password'] = password_hash($data['password'], PASSWORD_DEFAULT);
            
            $user = $this->userService->createUser($data);
            
            return $response->json([
                'success' => true,
                'data' => $user,
                'message' => 'User created successfully'
            ])->withStatus(201);
        } catch (\Exception $e) {
            return $response->json([
                'success' => false,
                'message' => 'Error creating user: ' . $e->getMessage()
            ])->withStatus(500);
        }
    }

    /**
     * @PutMapping(path="/{id}")
     */
    public function update(string $id, RequestInterface $request, ResponseInterface $response): PsrResponseInterface
    {
        try {
            $data = $request->all();
            
            // Don't allow updating password through this method unless specifically handled
            if (isset($data['password'])) {
                $data['password'] = password_hash($data['password'], PASSWORD_DEFAULT);
            }
            
            $result = $this->userService->updateUser($id, $data);
            
            if (!$result) {
                return $response->json([
                    'success' => false,
                    'message' => 'User not found'
                ])->withStatus(404);
            }
            
            $user = $this->userService->getUserById($id);
            
            return $response->json([
                'success' => true,
                'data' => $user,
                'message' => 'User updated successfully'
            ]);
        } catch (\Exception $e) {
            return $response->json([
                'success' => false,
                'message' => 'Error updating user: ' . $e->getMessage()
            ])->withStatus(500);
        }
    }

    /**
     * @DeleteMapping(path="/{id}")
     */
    public function destroy(string $id, ResponseInterface $response): PsrResponseInterface
    {
        try {
            $result = $this->userService->deleteUser($id);
            
            if (!$result) {
                return $response->json([
                    'success' => false,
                    'message' => 'User not found'
                ])->withStatus(404);
            }
            
            return $response->json([
                'success' => true,
                'message' => 'User deleted successfully'
            ]);
        } catch (\Exception $e) {
            return $response->json([
                'success' => false,
                'message' => 'Error deleting user: ' . $e->getMessage()
            ])->withStatus(500);
        }
    }

    /**
     * @GetMapping(path="/role/{roleName}")
     */
    public function getByRole(string $roleName, ResponseInterface $response): PsrResponseInterface
    {
        try {
            $users = $this->userService->getUsersByRole($roleName);
            
            return $response->json([
                'success' => true,
                'data' => $users,
                'count' => $users->count()
            ]);
        } catch (\Exception $e) {
            return $response->json([
                'success' => false,
                'message' => 'Error retrieving users by role: ' . $e->getMessage()
            ])->withStatus(500);
        }
    }
}