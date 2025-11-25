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

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
        $this->request = $container->get(RequestInterface::class);
        $this->response = $container->get(HttpResponse::class);
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        // Get the client IP address
        $clientIp = $this->getClientIp();
        
        // Create a unique key for rate limiting
        $rateLimitKey = 'rate_limit:' . $clientIp . ':' . $request->getMethod() . ':' . $request->getUri()->getPath();
        
        // Try to get Redis connection
        $redis = $this->getRedisConnection();
        if ($redis) {
            // Get current count and expiration time
            $data = $redis->get($rateLimitKey);
            $currentTime = time();
            $maxAttempts = (int)($_ENV['RATE_LIMIT_ATTEMPTS'] ?? 10); // Default 10 attempts
            $decayMinutes = (int)($_ENV['RATE_LIMIT_DECAY_MINUTES'] ?? 1); // Default 1 minute
            
            if ($data) {
                $data = json_decode($data, true);
                $attempts = $data['attempts'] ?? 0;
                $resetTime = $data['reset_time'] ?? $currentTime;
                
                if ($attempts >= $maxAttempts && $currentTime < $resetTime) {
                    // Rate limit exceeded
                    $remaining = 0;
                    $retryAfter = $resetTime - $currentTime;
                    
                    return $this->response->json([
                        'success' => false,
                        'message' => 'Too Many Attempts.',
                        'timestamp' => date('c'),
                        'data' => [
                            'attempts' => $attempts,
                            'max_attempts' => $maxAttempts,
                            'retry_after' => $retryAfter
                        ]
                    ])->withStatus(429)
                      ->withHeader('X-RateLimit-Limit', $maxAttempts)
                      ->withHeader('X-RateLimit-Remaining', $remaining)
                      ->withHeader('Retry-After', $retryAfter);
                }
                
                // Increment attempts
                $attempts++;
                $redis->setex($rateLimitKey, $decayMinutes * 60, json_encode([
                    'attempts' => $attempts,
                    'reset_time' => $resetTime
                ]));
            } else {
                // First attempt
                $redis->setex($rateLimitKey, $decayMinutes * 60, json_encode([
                    'attempts' => 1,
                    'reset_time' => $currentTime + ($decayMinutes * 60)
                ]));
            }
            
            // Add rate limit headers
            $remaining = max(0, $maxAttempts - 1);
            $this->response = $this->response
                ->withHeader('X-RateLimit-Limit', $maxAttempts)
                ->withHeader('X-RateLimit-Remaining', $remaining)
                ->withHeader('X-RateLimit-Reset', $currentTime + ($decayMinutes * 60));
        }
        
        return $handler->handle($request);
    }
    
    /**
     * Get client IP address
     */
    private function getClientIp(): string
    {
        $forwarded = $this->request->getHeaderLine('X-Forwarded-For');
        if ($forwarded) {
            return trim(explode(',', $forwarded)[0]);
        }
        
        $realIp = $this->request->getHeaderLine('X-Real-IP');
        if ($realIp) {
            return $realIp;
        }
        
        return $this->request->server('remote_addr', '127.0.0.1');
    }
    
    /**
     * Get Redis connection using environment configuration
     */
    private function getRedisConnection()
    {
        try {
            $redis = new \Redis();
            $redis->connect(
                $_ENV['REDIS_HOST'] ?? 'localhost',
                (int)($_ENV['REDIS_PORT'] ?? 6379)
            );
            
            $auth = $_ENV['REDIS_AUTH'] ?? null;
            if ($auth && $auth !== '(null)') {
                $redis->auth($auth);
            }
            
            $db = (int)($_ENV['REDIS_DB'] ?? 0);
            $redis->select($db);
            
            return $redis;
        } catch (\Exception $e) {
            // Log error or handle as needed
            return null;
        }
    }
}