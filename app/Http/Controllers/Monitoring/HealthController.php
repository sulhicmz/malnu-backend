<?php

declare(strict_types=1);

namespace App\Http\Controllers\Monitoring;

use App\Services\MonitoringService;
use Hyperf\HttpServer\Annotation\Controller;
use Hyperf\HttpServer\Annotation\GetMapping;
use Hyperf\HttpServer\Contract\ResponseInterface as HttpResponse;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseInterface;

#[Controller]
class HealthController
{
    protected MonitoringService $monitoringService;
    protected HttpResponse $response;

    public function __construct(ContainerInterface $container)
    {
        $this->monitoringService = $container->get(MonitoringService::class);
        $this->response = $container->get(HttpResponse::class);
    }

    #[GetMapping('/health')]
    public function index(): ResponseInterface
    {
        $health = $this->monitoringService->getBasicHealth();

        return $this->response->json([
            'success' => true,
            'data' => $health,
            'timestamp' => date('c')
        ])->withStatus($health['status'] === 'healthy' ? 200 : 503);
    }

    #[GetMapping('/health/detailed')]
    public function detailed(): ResponseInterface
    {
        $health = $this->monitoringService->getDetailedHealth();

        return $this->response->json([
            'success' => true,
            'data' => $health,
            'timestamp' => date('c')
        ])->withStatus($health['status'] === 'healthy' ? 200 : 503);
    }

    #[GetMapping('/monitoring/metrics')]
    public function metrics(): ResponseInterface
    {
        $metrics = $this->monitoringService->getMetrics();

        return $this->response->json([
            'success' => true,
            'data' => $metrics,
            'timestamp' => date('c')
        ]);
    }

    #[GetMapping('/monitoring/errors')]
    public function errors(): ResponseInterface
    {
        $errors = $this->monitoringService->getRecentErrors();

        return $this->response->json([
            'success' => true,
            'data' => $errors,
            'timestamp' => date('c')
        ]);
    }
}
