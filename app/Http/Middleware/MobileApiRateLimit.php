<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Hypervel\Http\Request;
use Hypervel\Support\Facades\Redis;
use Hypervel\Http\JsonResponse;

class MobileApiRateLimit
{
    /**
     * Handle an incoming request.
     *
     * @param  \Hypervel\Http\Request  $request
     * @param  \Closure  $next
     * @param  int  $maxAttempts
     * @param  int  $decayMinutes
     * @return mixed
     */
    public function handle(Request $request, Closure $next, $maxAttempts = 60, $decayMinutes = 1)
    {
        $key = $this->resolveRequestSignature($request);
        
        $maxAttempts = (int) $maxAttempts;
        $decayMinutes = (int) $decayMinutes;
        
        if (Redis::connection()->exists($key)) {
            $attempts = (int) Redis::connection()->get($key);
            
            if ($attempts >= $maxAttempts) {
                return response()->json([
                    'success' => false,
                    'message' => 'Too many requests. Please try again later.',
                    'retry_after' => $decayMinutes * 60
                ], 429);
            }
            
            Redis::connection()->incr($key);
        } else {
            Redis::connection()->setex($key, $decayMinutes * 60, 1);
        }
        
        $response = $next($request);
        
        // Add rate limit headers
        $response->headers->set('X-RateLimit-Limit', $maxAttempts);
        $response->headers->set('X-RateLimit-Remaining', $maxAttempts - (Redis::connection()->get($key) ?? 0));
        
        return $response;
    }
    
    /**
     * Resolve request signature.
     *
     * @param  \Hypervel\Http\Request  $request
     * @return string
     */
    protected function resolveRequestSignature($request)
    {
        $ip = $request->ip();
        $route = $request->route() ? $request->route()->getName() : $request->getPathInfo();
        
        return "mobile_api_rate_limit:{$ip}:{$route}";
    }
}