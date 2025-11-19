<?php

declare(strict_types=1);

namespace App\Middleware;

use App\Services\PerformanceMonitorService;
use Hyperf\Di\Annotation\Inject;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Hyperf\HttpServer\Contract\ResponseInterface as HttpResponse;

class PerformanceTrackingMiddleware implements MiddlewareInterface
{
    #[Inject]
    protected PerformanceMonitorService $performanceService;

    #[Inject]
    protected HttpResponse $response;

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $startTime = microtime(true);
        
        $response = $handler->handle($request);
        
        $executionTime = microtime(true) - $startTime;
        
        // Track the request execution time
        $this->trackRequestPerformance($request, $executionTime);
        
        return $response;
    }

    private function trackRequestPerformance(ServerRequestInterface $request, float $executionTime): void
    {
        $path = $request->getUri()->getPath();
        $method = $request->getMethod();
        $fullPath = $method . ' ' . $path;
        
        // Track query time in performance monitor
        $this->performanceService->trackQueryTime($fullPath, $executionTime);
    }
}