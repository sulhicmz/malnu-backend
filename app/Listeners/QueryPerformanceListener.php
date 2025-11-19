<?php

declare(strict_types=1);

namespace App\Listeners;

use App\Services\PerformanceMonitorService;
use Hyperf\Event\Annotation\Listener;
use Hyperf\Event\Contract\ListenerInterface;
use Hyperf\Database\Events\QueryExecuted;
use Hyperf\Di\Annotation\Inject;
use Psr\Log\LoggerInterface;

#[Listener]
class QueryPerformanceListener implements ListenerInterface
{
    #[Inject]
    protected PerformanceMonitorService $performanceService;

    #[Inject]
    protected LoggerInterface $logger;

    public function listen(): array
    {
        return [
            QueryExecuted::class,
        ];
    }

    public function process(object $event): void
    {
        if ($event instanceof QueryExecuted) {
            // Track query execution time
            $executionTime = $event->time / 1000; // Convert from milliseconds to seconds
            $sql = $event->sql;
            
            // Log slow queries
            if ($executionTime > 0.1) { // 100ms threshold
                $this->logger->warning('Slow query detected', [
                    'sql' => $sql,
                    'bindings' => $event->bindings,
                    'time' => $executionTime,
                    'connection' => $event->connectionName
                ]);
            }
            
            // Track in performance monitor
            $this->performanceService->trackQueryTime($sql, $executionTime);
        }
    }
}