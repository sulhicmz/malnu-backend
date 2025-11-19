<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Hypervel\Http\Request;
use Hypervel\Http\Response;
use Hypervel\Support\Facades\RateLimiter;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;

class ApiRateLimit
{
    /**
     * Handle an incoming request with rate limiting.
     *
     * @param  \Hypervel\Http\Request  $request
     * @param  \Closure  $next
     * @param  string|null  $guard
     * @return mixed
     */
    public function handle(Request $request, Closure $next, string $guard = null)
    {
        // Apply rate limiting based on IP address
        $key = 'api_requests_' . $request->ip();
        
        // Limit to 60 requests per minute for mobile API
        $maxAttempts = 60;
        $decayMinutes = 1;
        
        // For more specific rate limiting based on user authentication
        if ($request->user()) {
            $key = 'api_requests_user_' . $request->user()->id;
            // Authenticated users might have higher limits
            $maxAttempts = 100;
        }
        
        if (RateLimiter::tooManyAttempts($key, $maxAttempts)) {
            $seconds = RateLimiter::availableIn($key);
            
            return response()->json([
                'message' => 'Too many requests. Please try again later.',
                'retry_after' => $seconds,
            ], SymfonyResponse::HTTP_TOO_MANY_REQUESTS);
        }
        
        RateLimiter::hit($key, $decayMinutes * 60);
        
        $response = $next($request);
        
        // Add rate limit headers to response
        $response->headers->add([
            'X-RateLimit-Limit' => $maxAttempts,
            'X-RateLimit-Remaining' => max(0, $maxAttempts - RateLimiter::attempts($key)),
            'X-RateLimit-Reset' => now()->addSeconds(RateLimiter::availableIn($key))->timestamp,
        ]);
        
        return $response;
    }
}