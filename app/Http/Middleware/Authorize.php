<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Hypervel\Http\Response as HttpResponse;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class Authorize implements MiddlewareInterface
{
    protected ContainerInterface $container;
    protected HttpResponse $response;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
        $this->response = $container->get(HttpResponse::class);
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $user = $request->getAttribute('user');

        if (!$user) {
            return $this->response->json([
                'success' => false,
                'error' => [
                    'message' => 'Authentication required',
                    'code' => 'UNAUTHORIZED'
                ],
                'timestamp' => date('c')
            ])->withStatus(401);
        }

        $requiredPermission = $request->getAttribute('permission');

        if ($requiredPermission === null) {
            return $handler->handle($request);
        }

        $userId = $user['id'] ?? null;
        if (!$userId) {
            return $this->response->json([
                'success' => false,
                'error' => [
                    'message' => 'Invalid user data',
                    'code' => 'UNAUTHORIZED'
                ],
                'timestamp' => date('c')
            ])->withStatus(401);
        }

        $hasPermission = $this->userHasPermission($userId, $requiredPermission);

        if (!$hasPermission) {
            return $this->response->json([
                'success' => false,
                'error' => [
                    'message' => 'Access denied - insufficient permissions',
                    'code' => 'FORBIDDEN'
                ],
                'timestamp' => date('c')
            ])->withStatus(403);
        }

        return $handler->handle($request);
    }

    protected function userHasPermission(string $userId, string $permission): bool
    {
        $user = \App\Models\User::find($userId);

        if (!$user) {
            return false;
        }

        return $user->hasPermission($permission);
    }
}
