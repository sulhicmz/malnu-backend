<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Contracts\AuthServiceInterface;
use App\Models\Role;
use App\Models\User;

class RoleMiddleware
{
    private AuthServiceInterface $authService;

    public function __construct()
    {
        $this->authService = new \App\Services\AuthService();
    }

    public function handle($request, $next, $role)
    {
        $authHeader = $request->getHeaderLine('Authorization');
        
        if (!$authHeader || !str_starts_with($authHeader, 'Bearer ')) {
            return $this->unauthorizedResponse('Authorization token required');
        }

        $token = substr($authHeader, 7); // Remove 'Bearer ' prefix

        $user = $this->authService->getUserFromToken($token);

        if (!$user) {
            return $this->unauthorizedResponse('Invalid or expired token');
        }

        $userModel = User::find($user['id']);

        if (!$userModel) {
            return $this->unauthorizedResponse('User not found');
        }

        $hasRole = $this->userHasRole($userModel, $role);

        if (!$hasRole) {
            return $this->forbiddenResponse('Insufficient permissions');
        }

        return $next($request);
    }

    private function userHasRole($user, $requiredRole)
    {
        $requiredRoles = explode('|', $requiredRole);
        return $user->hasAnyRole($requiredRoles);
    }
    
    private function unauthorizedResponse($message)
    {
        $response = new \Hyperf\HttpMessage\Server\Response();
        return $response->withStatus(401)->withHeader('Content-Type', 'application/json')
            ->withBody(new \Hyperf\HttpMessage\Stream\SwooleStream(json_encode([
                'success' => false,
                'error' => [
                    'message' => $message,
                    'code' => 'UNAUTHORIZED'
                ],
                'timestamp' => date('c')
            ])));
    }
    
    private function forbiddenResponse($message)
    {
        $response = new \Hyperf\HttpMessage\Server\Response();
        return $response->withStatus(403)->withHeader('Content-Type', 'application/json')
            ->withBody(new \Hyperf\HttpMessage\Stream\SwooleStream(json_encode([
                'success' => false,
                'error' => [
                    'message' => $message,
                    'code' => 'FORBIDDEN'
                ],
                'timestamp' => date('c')
            ])));
    }
}