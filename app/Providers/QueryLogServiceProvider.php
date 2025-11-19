<?php

declare(strict_types=1);

namespace App\Providers;

use Hypervel\Support\Facades\DB;
use Hypervel\Support\Facades\Log;
use Hypervel\Support\ServiceProvider;

class QueryLogServiceProvider extends ServiceProvider
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
        // Enable query logging in debug mode
        if (config('app.debug', false)) {
            DB::enableQueryLog();
            
            // Log slow queries
            DB::listen(function ($query) {
                $executionTime = $query->time;
                
                // Log queries that take longer than 100ms
                if ($executionTime > 100) {
                    Log::warning('Slow Query Detected', [
                        'sql' => $query->sql,
                        'bindings' => $query->bindings,
                        'time' => $executionTime . 'ms',
                        'connection' => $query->connectionName,
                    ]);
                }
                
                // Log all queries in debug mode for analysis
                if (config('database.log_all_queries', false)) {
                    Log::debug('Query Executed', [
                        'sql' => $query->sql,
                        'bindings' => $query->bindings,
                        'time' => $executionTime . 'ms',
                        'connection' => $query->connectionName,
                    ]);
                }
            });
        }
    }
}