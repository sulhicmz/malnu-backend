<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Hypervel\Http\Request;
use Hypervel\Support\Facades\Log;

class PerformanceMonitoringMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Hypervel\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        $startTime = microtime(true);
        
        $response = $next($request);
        
        $executionTime = (microtime(true) - $startTime) * 1000; // in milliseconds
        
        // Log slow requests (those taking more than 200ms)
        if ($executionTime > 200) {
            Log::warning('Slow Request Detected', [
                'method' => $request->method(),
                'url' => $request->fullUrl(),
                'execution_time' => round($executionTime, 2) . 'ms',
                'status_code' => $response->getStatusCode(),
                'ip' => $request->ip(),
            ]);
        }
        
        // Add performance header to response (only in debug mode)
        if (config('app.debug', false)) {
            $response->headers->set('X-Response-Time', round($executionTime, 2) . 'ms');
        }
        
        return $response;
    }
}