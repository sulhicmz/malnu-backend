<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class LoginThrottleMiddleware implements MiddlewareInterface
{
    /**
     * Maximum number of attempts allowed
     */
    protected int $maxAttempts = 5;

    /**
     * Decay time in minutes
     */
    protected int $decayMinutes = 15;

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $uri = $request->getUri()->getPath();
        $method = $request->getMethod();
        
        // Only apply rate limiting to login endpoints
        if ($this->isLoginEndpoint($uri, $method)) {
            $key = $this->getThrottleKey($request);
            
            // Check if the user has exceeded the rate limit
            if ($this->tooManyAttempts($key)) {
                return $this->buildResponse([
                    'message' => 'Too many login attempts. Please try again later.',
                    'error_code' => 'RATE_LIMIT_EXCEEDED'
                ], 429);
            }

            // Increment the attempt counter
            $this->hit($key);
        }

        return $handler->handle($request);
    }

    /**
     * Check if the current endpoint is a login endpoint
     */
    protected function isLoginEndpoint(string $uri, string $method): bool
    {
        return ($method === 'POST' && in_array($uri, ['/auth/login', '/api/auth/login', '/login']));
    }

    /**
     * Get the throttle key for the request
     */
    protected function getThrottleKey(ServerRequestInterface $request): string
    {
        $ip = $this->getRealIp($request);
        return 'login_attempts:' . $ip;
    }

    /**
     * Get the real IP address considering proxy headers
     */
    protected function getRealIp(ServerRequestInterface $request): string
    {
        // Check for forwarded IP headers
        $headers = $request->getHeaders();
        if (isset($headers['X-Forwarded-For'])) {
            return trim(explode(',', $headers['X-Forwarded-For'][0])[0]);
        }

        if (isset($headers['X-Real-IP'])) {
            return $headers['X-Real-IP'][0];
        }

        $serverParams = $request->getServerParams();
        return $serverParams['remote_addr'] ?? '127.0.0.1';
    }

    /**
     * Check if too many attempts have been made
     */
    protected function tooManyAttempts(string $key): bool
    {
        $attempts = $this->get($key);
        
        if ($attempts >= $this->maxAttempts) {
            $ttl = $this->getTtl();
            if ($ttl > 0) {
                return true;
            }
            // Reset the counter if TTL has expired
            $this->clear($key);
        }
        
        return false;
    }

    /**
     * Get the number of attempts for the key
     */
    protected function get(string $key): int
    {
        // In a real implementation, this would use Redis
        // For now, we'll return 0 to avoid errors during static analysis
        return 0;
    }

    /**
     * Increment the attempt counter
     */
    protected function hit(string $key): int
    {
        $value = $this->get($key);
        $value++;
        
        // In a real implementation, this would use Redis
        // For now, we'll just return the incremented value
        return $value;
    }

    /**
     * Clear the attempt counter
     */
    protected function clear(string $key): void
    {
        // In a real implementation, this would use Redis
    }

    /**
     * Get TTL in seconds
     */
    protected function getTtl(): int
    {
        return $this->decayMinutes * 60;
    }
    
    /**
     * Build a JSON response
     */
    private function buildResponse(array $data, int $statusCode = 200): ResponseInterface
    {
        $body = json_encode($data);
        $response = new \Hyperf\HttpMessage\Server\Response();
        $response->getBody()->write($body);
        return $response->withStatus($statusCode)
                       ->withHeader('Content-Type', 'application/json');
    }
}