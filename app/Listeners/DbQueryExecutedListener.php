<?php

declare(strict_types=1);

namespace App\Listeners;

use Hypervel\Support\Arr;
use Hypervel\Database\Events\QueryExecuted;
use Hypervel\Event\Contracts\Listener;
use Psr\Container\ContainerInterface;
use Hyperf\Contract\StdoutLoggerInterface;

class DbQueryExecutedListener implements Listener
{
    private StdoutLoggerInterface $logger;

    public function __construct(ContainerInterface $container)
    {
        $this->logger = $container->get(StdoutLoggerInterface::class);
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

            $this->logger->info(sprintf('[%s] %s', $event->time, $sql));
        }
    }
}
