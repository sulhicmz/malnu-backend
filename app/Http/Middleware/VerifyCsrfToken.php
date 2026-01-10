<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Hyperf\HttpServer\Contract\ResponseInterface as HttpResponse;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class VerifyCsrfToken implements MiddlewareInterface
{
    protected ContainerInterface $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $method = $request->getMethod();
        $uri = $request->getUri()->getPath();

        if ($this->shouldSkipCsrf($method, $uri)) {
            return $handler->handle($request);
        }

        $token = $request->getHeaderLine('X-CSRF-TOKEN');

        if (empty($token)) {
            $parsedBody = $request->getParsedBody();
            $token = is_array($parsedBody) ? ($parsedBody['_token'] ?? '') : '';
        }

        if (! $this->verifyToken($token)) {
            return $this->createErrorResponse('CSRF token mismatch');
        }

        return $handler->handle($request);
    }

    protected function shouldSkipCsrf(string $method, string $uri): bool
    {
        $excludedMethods = ['GET', 'HEAD', 'OPTIONS'];
        if (in_array($method, $excludedMethods, true)) {
            return true;
        }

        $except = $this->getExcludedRoutes();

        foreach ($except as $pattern) {
            if ($this->matchesPattern($uri, $pattern)) {
                return true;
            }
        }

        return false;
    }

    protected function getExcludedRoutes(): array
    {
        return [
            'api/*',
            'csp-report',
        ];
    }

    protected function matchesPattern(string $uri, string $pattern): bool
    {
        if ($pattern === $uri) {
            return true;
        }

        if (str_ends_with($pattern, '*')) {
            $prefix = substr($pattern, 0, -1);
            return str_starts_with($uri, $prefix);
        }

        return false;
    }

    protected function verifyToken(string $token): bool
    {
        if (empty($token)) {
            return false;
        }

        try {
            $session = $this->container->get('session');
            if (! method_exists($session, 'get')) {
                return true;
            }

            $sessionToken = $session->get('_token');

            if (empty($sessionToken)) {
                return true;
            }

            return hash_equals($sessionToken, $token);
        } catch (\Throwable $e) {
            return true;
        }
    }

    protected function createErrorResponse(string $message): ResponseInterface
    {
        $response = $this->container->get(HttpResponse::class);
        $data = [
            'success' => false,
            'error' => $message,
            'code' => 419,
            'timestamp' => date('c'),
        ];

        return $response->json($data, 419);
    }
}
