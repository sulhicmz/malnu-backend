<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Contracts\AuthServiceInterface;
use Hypervel\Http\Request;
use Hypervel\Http\Response as HttpResponse;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class JWTMiddleware implements MiddlewareInterface
{
    protected ContainerInterface $container;

    protected RequestInterface $request;

    protected HttpResponse $response;

    protected AuthServiceInterface $authService;

    public function __construct(
        ContainerInterface $container,
        AuthServiceInterface $authService
    ) {
        $this->container = $container;
        $this->request = $container->get(RequestInterface::class);
        $this->response = $container->get(HttpResponse::class);
        $this->authService = $authService;
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $authHeader = $request->getHeaderLine('Authorization');

        if (! $authHeader || ! str_starts_with($authHeader, 'Bearer ')) {
            return $this->response->json([
                'success' => false,
                'error' => [
                    'message' => 'Authorization token required',
                    'code' => 'UNAUTHORIZED'
                ],
                'timestamp' => date('c')
            ])->withStatus(401);
        }

        $token = substr($authHeader, 7); // Remove 'Bearer ' prefix

        // Check if token is blacklisted or invalid
        $user = $this->authService->getUserFromToken($token);
        if ($user === null) {
            return $this->response->json([
                'success' => false,
                'error' => [
                    'message' => 'Invalid or expired token',
                    'code' => 'UNAUTHORIZED'
                ],
                'timestamp' => date('c')
            ])->withStatus(401);
        }

        // Add token and user to request attributes for later use
        $request = $request->withAttribute('token', $token);
        $request = $request->withAttribute('user', $user);

        return $handler->handle($request);
    }
}
