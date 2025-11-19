<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Services\PerformanceMonitorService;
use Hyperf\Di\Annotation\Inject;
use Hyperf\HttpServer\Contract\RequestInterface;
use Hyperf\HttpServer\Contract\ResponseInterface;

class PerformanceController
{
    #[Inject]
    protected PerformanceMonitorService $performanceService;

    public function getPerformanceReport(RequestInterface $request, ResponseInterface $response)
    {
        $report = $this->performanceService->getPerformanceReport();
        return $response->json($report);
    }

    public function getCacheStats(RequestInterface $request, ResponseInterface $response)
    {
        $stats = $this->performanceService->getCacheStats();
        return $response->json($stats);
    }

    public function getQueryStats(RequestInterface $request, ResponseInterface $response)
    {
        $stats = $this->performanceService->getQueryStats();
        return $response->json($stats);
    }

    public function resetStats(RequestInterface $request, ResponseInterface $response)
    {
        $this->performanceService->resetStats();
        return $response->json(['message' => 'Performance statistics reset successfully']);
    }
}