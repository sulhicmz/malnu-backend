<?php

declare(strict_types=1);

namespace App\Listeners;

use App\Utils\PerformanceMonitor;
use Hyperf\Collection\Arr;
use Hyperf\Database\Events\QueryExecuted;
use Hyperf\Event\Contract\ListenerInterface;
use Hyperf\Framework\Logger\StdoutLogger;
use Psr\Container\ContainerInterface;
use Psr\Log\LoggerInterface;

class DbQueryExecutedListener implements ListenerInterface
{
    private LoggerInterface $logger;

    public function __construct(ContainerInterface $container)
    {
        $this->logger = $container->get(StdoutLogger::class);
    }

    public function listen(): array
    {
        return [
            QueryExecuted::class,
        ];
    }

    /**
     * @param QueryExecuted $event
     */
    public function process(object $event): void
    {
        if ($event instanceof QueryExecuted) {
            $sql = $event->sql;
            if (! Arr::isAssoc($event->bindings)) {
                $position = 0;
                foreach ($event->bindings as $value) {
                    $position = strpos($sql, '?', $position);
                    if ($position === false) {
                        break;
                    }
                    $value = "'{$value}'";
                    $sql = substr_replace($sql, $value, $position, 1);
                    $position += strlen($value);
                }
            }

            // Log to performance monitor
            PerformanceMonitor::logQuery($sql, $event->time / 1000, $event->bindings); // Convert microseconds to seconds

            // Log slow queries (queries taking more than 100ms)
            if (($event->time / 1000) > 0.1) { // 100ms threshold
                $this->logger->warning(sprintf(
                    '[SLOW QUERY - %s ms] %s',
                    number_format($event->time, 2),
                    $sql
                ));
            } else {
                $this->logger->info(sprintf('[%s ms] %s', number_format($event->time, 2), $sql));
            }
        }
    }
}
