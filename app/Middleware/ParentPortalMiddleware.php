<?php

declare(strict_types=1);

namespace App\Middleware;

use Hyperf\HttpServer\Contract\RequestInterface;
use Hyperf\HttpServer\Contract\ResponseInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Container\ContainerInterface;

class ParentPortalMiddleware implements MiddlewareInterface
{
    public function __construct(
        private ContainerInterface $container
    ) {}

    public function process(RequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $user = $request->getAttribute('user');

        if (!$user) {
            return $this->unauthorizedResponse('User not authenticated');
        }

        if (!$this->isParent($user)) {
            return $this->forbiddenResponse('Access denied. Parent role required.');
        }

        return $handler->handle($request);
    }

    private function isParent(array $user): bool
    {
        return isset($user['role']) && $user['role'] === 'parent';
    }

    private function unauthorizedResponse(string $message): ResponseInterface
    {
        $response = $this->container->get(ResponseInterface::class);
        return $response->json([
            'success' => false,
            'error' => [
                'message' => $message,
                'code' => 'UNAUTHORIZED',
            ],
            'timestamp' => date('c'),
        ])->withStatus(401);
    }

    private function forbiddenResponse(string $message): ResponseInterface
    {
        $response = $this->container->get(ResponseInterface::class);
        return $response->json([
            'success' => false,
            'error' => [
                'message' => $message,
                'code' => 'FORBIDDEN',
            ],
            'timestamp' => date('c'),
        ])->withStatus(403);
    }
}
