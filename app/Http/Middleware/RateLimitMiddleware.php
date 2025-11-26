<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Hyperf\HttpServer\Contract\RequestInterface;
use Hyperf\HttpServer\Contract\ResponseInterface as HttpResponse;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class RateLimitMiddleware implements MiddlewareInterface
{
    protected ContainerInterface $container;
    protected RequestInterface $request;
    protected HttpResponse $response;
    
    // Default rate limit: 60 requests per minute
    private int $maxAttempts = 60;
    private int $decayMinutes = 1;
    private array $rateLimits = []; // Simple in-memory storage for rate limiting

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
        $this->request = $container->get(RequestInterface::class);
        $this->response = $container->get(HttpResponse::class);
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $key = $this->resolveRequestKey($request);
        $maxAttempts = $this->resolveMaxAttempts($request);
        
        // Clean expired entries
        $this->cleanExpired();
        
        // Check if rate limit exceeded
        if ($this->tooManyAttempts($key, $maxAttempts)) {
            return $this->buildRateLimitResponse($key, $maxAttempts);
        }

        // Increment request count
        $this->hit($key, $this->decayMinutes * 60);
        
        return $handler->handle($request);
    }

    protected function resolveRequestKey(ServerRequestInterface $request): string
    {
        $ip = $request->getHeaderLine('X-Forwarded-For') ?: 
              $request->getHeaderLine('X-Real-IP') ?: 
              $request->server('remote_addr', '127.0.0.1');
        
        // Create a key based on IP and route
        $route = $request->getMethod() . ':' . $request->getUri()->getPath();
        
        return md5($ip . ':' . $route);
    }

    protected function resolveMaxAttempts(ServerRequestInterface $request): int
    {
        // Different endpoints might have different rate limits
        $path = $request->getUri()->getPath();
        
        // More restrictive for auth endpoints
        if (strpos($path, '/api/auth') !== false || strpos($path, '/login') !== false) {
            return (int)($_ENV['RATE_LIMIT_LOGIN'] ?? 10); // 10 attempts per minute for login
        }
        
        // Default rate limit
        return (int)($_ENV['RATE_LIMIT_DEFAULT'] ?? $this->maxAttempts);
    }

    protected function tooManyAttempts(string $key, int $maxAttempts): bool
    {
        if (!isset($this->rateLimits[$key])) {
            return false;
        }
        
        $attempts = $this->rateLimits[$key]['attempts'];
        $expiresAt = $this->rateLimits[$key]['expires_at'];
        
        // If expired, reset
        if (time() >= $expiresAt) {
            unset($this->rateLimits[$key]);
            return false;
        }
        
        return $attempts >= $maxAttempts;
    }

    protected function hit(string $key, int $decaySeconds): void
    {
        $expiresAt = time() + $decaySeconds;
        
        if (isset($this->rateLimits[$key])) {
            $this->rateLimits[$key]['attempts']++;
        } else {
            $this->rateLimits[$key] = [
                'attempts' => 1,
                'expires_at' => $expiresAt
            ];
        }
    }

    protected function cleanExpired(): void
    {
        $currentTime = time();
        foreach ($this->rateLimits as $key => $data) {
            if ($currentTime >= $data['expires_at']) {
                unset($this->rateLimits[$key]);
            }
        }
    }

    protected function buildRateLimitResponse(string $key, int $maxAttempts): ResponseInterface
    {
        if (!isset($this->rateLimits[$key])) {
            $retryAfter = $this->decayMinutes * 60;
        } else {
            $retryAfter = $this->rateLimits[$key]['expires_at'] - time();
        }
        
        return $this->response->json([
            'success' => false,
            'message' => 'Too many requests. Please try again later.',
            'error' => [
                'code' => 'RATE_LIMIT_EXCEEDED',
                'message' => 'Rate limit exceeded. Maximum ' . $maxAttempts . ' requests per minute allowed.'
            ],
            'retry_after' => $retryAfter,
            'timestamp' => date('c')
        ])->withStatus(429)
          ->withHeader('Retry-After', $retryAfter)
          ->withHeader('X-RateLimit-Limit', $maxAttempts)
          ->withHeader('X-RateLimit-Remaining', 0)
          ->withHeader('X-RateLimit-Reset', time() + $retryAfter);
    }
}