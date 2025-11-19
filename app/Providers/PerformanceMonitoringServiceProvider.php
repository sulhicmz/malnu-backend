<?php

declare(strict_types=1);

namespace App\Providers;

use Hypervel\Cache\Events\CacheHit;
use Hypervel\Cache\Events\CacheMissed;
use Hypervel\Cache\Events\KeyForgotten;
use Hypervel\Cache\Events\KeyWritten;
use Hypervel\Database\Events\QueryExecuted;
use Hypervel\Support\Facades\Cache;
use Hypervel\Support\Facades\DB;
use Hypervel\Support\ServiceProvider;
use Illuminate\Support\Str;

class PerformanceMonitoringServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // Log slow queries
        if (app()->environment('local', 'development')) {
            DB::listen(function (QueryExecuted $event) {
                $executionTime = $event->time;
                
                // Log queries that take longer than 100ms
                if ($executionTime > 100) {
                    \Hypervel\Support\Facades\Log::warning('Slow Query Detected', [
                        'sql' => $event->sql,
                        'bindings' => $event->bindings,
                        'time' => $executionTime . 'ms',
                        'connection' => $event->connectionName,
                    ]);
                }
            });
        }

        // Cache hit/miss monitoring
        if (app()->environment('local', 'development')) {
            Cache::events()->listen(CacheHit::class, function ($event) {
                \Hypervel\Support\Facades\Log::debug('Cache Hit', [
                    'key' => $event->key,
                    'tags' => $event->tags ?? [],
                ]);
            });

            Cache::events()->listen(CacheMissed::class, function ($event) {
                \Hypervel\Support\Facades\Log::debug('Cache Miss', [
                    'key' => $event->key,
                    'tags' => $event->tags ?? [],
                ]);
            });
        }
    }
}