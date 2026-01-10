<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Contracts\AuthServiceInterface;
use App\Models\User;
use Hyperf\HttpServer\Contract\RequestInterface;
use Hyperf\HttpServer\Contract\ResponseInterface as HttpResponse;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface as PsrResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Message\ResponseInterface as PsrResponseInterface;
use Psr\Http\Server\RequestHandlerInterface;

class RoleMiddleware implements MiddlewareInterface
{
    protected ContainerInterface $container;
    protected RequestInterface $request;
    protected HttpResponse $response;
    protected AuthServiceInterface $authService;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
        $this->request = $container->get(RequestInterface::class);
        $this->response = $container->get(HttpResponse::class);
        $this->authService = new \App\Services\AuthService();
    }

    public function handle(ServerRequestInterface $request, RequestHandlerInterface $next, $role = null)
    {
        $authHeader = $request->getHeaderLine('Authorization');
        
        if (!$authHeader || !str_starts_with($authHeader, 'Bearer ')) {
            return $this->unauthorizedResponse('Authorization token required');
        }
        
        $token = substr($authHeader, 7);
        
        $user = $this->authService->getUserFromToken($token);
        
        if (!$user) {
            return $this->unauthorizedResponse('Invalid or expired token');
        }
        
        if (!$this->userHasRequiredRole($user, $role)) {
            return $this->forbiddenResponse('Insufficient permissions');
        }
        
        return $next($request);
    }
    
    private function userHasRequiredRole(User $user, string $requiredRole): bool
    {
        $requiredRoles = explode('|', $requiredRole);
        
        foreach ($requiredRoles as $requiredRole) {
            if ($user->hasRole($requiredRole)) {
                return true;
            }
        }
        
        return false;
    }
    
    private function unauthorizedResponse(string $message): PsrResponseInterface
    {
        return $this->response->json([
            'success' => false,
            'error' => [
                'message' => $message,
                'code' => 'UNAUTHORIZED'
            ],
            'timestamp' => date('c')
        ])->withStatus(401)->withHeader('Content-Type', 'application/json');
    }
    
    private function forbiddenResponse(string $message): PsrResponseInterface
    {
        return $this->response->json([
            'success' => false,
            'error' => [
                'message' => $message,
                'code' => 'FORBIDDEN'
            ],
            'timestamp' => date('c')
        ])->withStatus(403)->withHeader('Content-Type', 'application/json');
    }
}
