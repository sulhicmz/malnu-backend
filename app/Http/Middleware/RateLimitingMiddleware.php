<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Hyperf\HttpServer\Contract\RequestInterface;
use Hyperf\HttpServer\Contract\ResponseInterface as HttpResponse;
use Hyperf\Redis\Redis;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class RateLimitingMiddleware implements MiddlewareInterface
{
    protected ContainerInterface $container;

    protected RequestInterface $request;

    protected HttpResponse $response;

    protected Redis $redis;

    protected array $config;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
        $this->request = $container->get(RequestInterface::class);
        $this->response = $container->get(HttpResponse::class);
        $this->redis = $container->get(Redis::class);
        $this->config = config('rate-limiting');
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $route = $request->getUri()->getPath();
        $method = $request->getMethod();

        $limitName = $this->getLimitNameForRoute($route, $method);

        if ($limitName === null) {
            return $handler->handle($request);
        }

        $limitConfig = $this->config['limits'][$limitName] ?? $this->config['limits']['public_api'];

        $key = $this->getRateLimitKey($request, $limitConfig['key_type']);
        $maxAttempts = $limitConfig['max_attempts'];
        $decayMinutes = $limitConfig['decay_minutes'];

        $attempts = $this->getAttempts($key, $decayMinutes);
        $remaining = max(0, $maxAttempts - $attempts);
        $resetTime = time() + ($decayMinutes * 60);

        if ($attempts >= $maxAttempts) {
            $retryAfter = $this->getRetryAfterTime($key);

            return $this->response->json([
                'success' => false,
                'error' => [
                    'message' => 'Too many requests. Please try again later.',
                    'code' => 'TOO_MANY_REQUESTS',
                ],
                'timestamp' => date('c'),
            ])
                ->withStatus(429)
                ->withHeader($this->config['headers']['limit'], (string) $maxAttempts)
                ->withHeader($this->config['headers']['remaining'], '0')
                ->withHeader($this->config['headers']['reset'], (string) $resetTime)
                ->withHeader($this->config['headers']['retry_after'], (string) $retryAfter);
        }

        $this->incrementAttempts($key, $decayMinutes);

        $response = $handler->handle($request);

        return $response
            ->withHeader($this->config['headers']['limit'], (string) $maxAttempts)
            ->withHeader($this->config['headers']['remaining'], (string) ($remaining - 1))
            ->withHeader($this->config['headers']['reset'], (string) $resetTime);
    }

    protected function getLimitNameForRoute(string $route, string $method): ?string
    {
        if ($method === 'POST' && str_ends_with($route, '/auth/login')) {
            return 'auth.login';
        }

        if ($method === 'POST' && str_ends_with($route, '/auth/register')) {
            return 'auth.register';
        }

        if ($method === 'POST' && str_ends_with($route, '/auth/password/reset')) {
            return 'auth.password.reset';
        }

        if ($method === 'POST' && str_ends_with($route, '/auth/password/forgot')) {
            return 'auth.password.forgot';
        }

        if ($route === '/auth/logout' || $route === '/auth/refresh' || $route === '/auth/me' || $route === '/auth/password/change') {
            return 'protected_api';
        }

        if (str_starts_with($route, '/attendance/') || str_starts_with($route, '/school/') || str_starts_with($route, '/calendar/')) {
            return 'protected_api';
        }

        if (str_starts_with($route, '/auth/')) {
            return 'public_api';
        }

        return 'public_api';
    }

    protected function getRateLimitKey(ServerRequestInterface $request, string $keyType): string
    {
        $prefix = $this->config['redis']['prefix'];
        $ip = $this->getClientIp($request);

        if ($keyType === 'user' && $request->getAttribute('user')) {
            $userId = $request->getAttribute('user')['id'] ?? 'guest';
            return $prefix . 'user:' . $userId;
        }

        if ($keyType === 'both') {
            $userId = $request->getAttribute('user') ? $request->getAttribute('user')['id'] : 'guest';
            return $prefix . 'both:' . $ip . ':' . $userId;
        }

        return $prefix . 'ip:' . $ip;
    }

    protected function getClientIp(ServerRequestInterface $request): string
    {
        $serverParams = $request->getServerParams();

        $headers = [
            'HTTP_X_FORWARDED_FOR',
            'HTTP_CLIENT_IP',
            'HTTP_X_REAL_IP',
            'REMOTE_ADDR',
        ];

        foreach ($headers as $header) {
            if (isset($serverParams[$header])) {
                $ipList = explode(',', $serverParams[$header]);
                if (! empty($ipList)) {
                    return trim($ipList[0]);
                }
            }
        }

        return $serverParams['REMOTE_ADDR'] ?? '0.0.0.0';
    }

    protected function getAttempts(string $key, int $decayMinutes): int
    {
        $attempts = $this->redis->get($key);

        return $attempts ? (int) $attempts : 0;
    }

    protected function incrementAttempts(string $key, int $decayMinutes): void
    {
        $this->redis->incr($key);
        $this->redis->expire($key, $decayMinutes * 60);
    }

    protected function getRetryAfterTime(string $key): int
    {
        $ttl = $this->redis->ttl($key);

        return $ttl > 0 ? $ttl : 60;
    }
}
