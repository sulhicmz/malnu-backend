<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Repositories\UserRepository;
use Hyperf\Di\Annotation\Inject;
use Hyperf\HttpServer\Contract\RequestInterface;
use Hyperf\HttpServer\Contract\ResponseInterface;

class UserController
{
    #[Inject]
    protected UserRepository $userRepository;

    public function index(RequestInterface $request, ResponseInterface $response)
    {
        $users = $this->userRepository->getAll();
        return $response->json($users);
    }

    public function show(string $id, RequestInterface $request, ResponseInterface $response)
    {
        $user = $this->userRepository->findById($id);
        
        if (!$user) {
            return $response->json(['error' => 'User not found'], 404);
        }
        
        return $response->json($user);
    }

    public function store(RequestInterface $request, ResponseInterface $response)
    {
        $data = $request->all();
        
        // Validate required fields
        if (empty($data['email']) || empty($data['name'])) {
            return $response->json(['error' => 'Email and name are required'], 400);
        }
        
        try {
            $user = $this->userRepository->create($data);
            return $response->json($user, 201);
        } catch (\Exception $e) {
            return $response->json(['error' => $e->getMessage()], 400);
        }
    }

    public function update(string $id, RequestInterface $request, ResponseInterface $response)
    {
        $data = $request->all();
        
        try {
            $user = $this->userRepository->update($id, $data);
            return $response->json($user);
        } catch (\Exception $e) {
            return $response->json(['error' => $e->getMessage()], 400);
        }
    }

    public function destroy(string $id, ResponseInterface $response)
    {
        $result = $this->userRepository->delete($id);
        
        if (!$result) {
            return $response->json(['error' => 'User not found'], 404);
        }
        
        return $response->json(['message' => 'User deleted successfully']);
    }

    public function paginate(RequestInterface $request, ResponseInterface $response)
    {
        $perPage = (int) $request->input('per_page', 15);
        $users = $this->userRepository->paginate($perPage);
        return $response->json($users);
    }
}