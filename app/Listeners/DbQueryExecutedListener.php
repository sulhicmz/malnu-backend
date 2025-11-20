<?php

declare(strict_types=1);

namespace App\Listeners;

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
            $time = $event->time;
            
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

            // Log slow queries (queries taking more than 100ms)
            if ($time > 100) {
                $this->logger->warning(sprintf('Slow query detected [%s ms]: %s', $time, $sql));
            } else {
                $this->logger->info(sprintf('[%s ms] %s', $time, $sql));
            }
        }
    }
}
