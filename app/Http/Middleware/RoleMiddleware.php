<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Contracts\AuthServiceInterface;
use App\Models\Role;
use App\Models\User;

class RoleMiddleware
{
    private AuthServiceInterface $authService;

    public function __construct(AuthServiceInterface $authService)
    {
        $this->authService = $authService;
    }

    public function handle($request, $next, $role)
    {
        $authHeader = $request->getHeaderLine('Authorization');

        if (! $authHeader || ! str_starts_with($authHeader, 'Bearer ')) {
            return $this->unauthorizedResponse('Authorization token required');
        }

        $token = substr($authHeader, 7);

        $user = $this->authService->getUserFromToken($token);

        if (! $user) {
            return $this->unauthorizedResponse('Invalid or expired token');
        }

        $userId = $user['id'] ?? null;
        if (! $userId) {
            return $this->unauthorizedResponse('Invalid user data');
        }

        $hasRole = $this->userHasRole($userId, $role);

        if (! $hasRole) {
            return $this->forbiddenResponse('Insufficient permissions');
        }

        return $next($request);
    }

    private function userHasRole(string $userId, string $requiredRole): bool
    {
        $user = User::find($userId);

        if (!$user) {
            return false;
        }

        $requiredRoles = explode('|', $requiredRole);
        return $user->hasAnyRole($requiredRoles);
    }

    private function unauthorizedResponse($message)
    {
        return $this->createErrorResponse($message, 401, 'UNAUTHORIZED');
    }

    private function forbiddenResponse($message)
    {
        return $this->createErrorResponse($message, 403, 'FORBIDDEN');
    }

    private function createErrorResponse(string $message, int $statusCode, string $errorCode)
    {
        $body = json_encode([
            'success' => false,
            'error' => [
                'message' => $message,
                'code' => $errorCode,
            ],
            'timestamp' => date('c'),
        ]);

        $response = new \Hyperf\HttpMessage\Server\Response();
        return $response
            ->withStatus($statusCode)
            ->withHeader('Content-Type', 'application/json')
            ->withBody(new \Hyperf\HttpMessage\Stream\SwooleStream($body));
    }
}
