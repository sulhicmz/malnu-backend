<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Services\ErrorLoggingService;
use Closure;

class PerformanceMonitoringMiddleware
{
    private ErrorLoggingService $errorLoggingService;

    public function __construct()
    {
        $this->errorLoggingService = new ErrorLoggingService();
    }

    public function process($request, Closure $next)
    {
        $startTime = microtime(true);

        $response = $next($request);

        $executionTime = microtime(true) - $startTime;

        // Extract request information if possible
        $method = 'unknown';
        $path = 'unknown';
        $userAgent = 'unknown';
        $ipAddress = 'unknown';

        if (method_exists($request, 'getMethod')) {
            $method = $request->getMethod();
        }

        if (method_exists($request, 'getUri')) {
            $uri = $request->getUri();
            if ($uri && method_exists($uri, 'getPath')) {
                $path = $uri->getPath();
            }
        }

        if (method_exists($request, 'getHeaderLine')) {
            $userAgent = $request->getHeaderLine('User-Agent');
        }

        if (method_exists($request, 'getServerParams')) {
            $serverParams = $request->getServerParams();
            $ipAddress = $serverParams['remote_addr'] ?? 'unknown';
        }

        // Log performance metrics
        $this->errorLoggingService->logPerformance(
            $method . ' ' . $path,
            $executionTime,
            [
                'status_code' => method_exists($response, 'getStatusCode') ? $response->getStatusCode() : 'unknown',
                'user_agent' => $userAgent,
                'ip_address' => $ipAddress,
            ]
        );

        return $response;
    }
}
