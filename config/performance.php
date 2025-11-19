<?php

declare(strict_types=1);

return [
    /*
    |--------------------------------------------------------------------------
    | Performance Monitoring Configuration
    |--------------------------------------------------------------------------
    |
    | This configuration defines settings for performance monitoring
    | including cache performance, query optimization, and metrics tracking.
    |
    */

    'monitoring' => [
        'enabled' => env('PERFORMANCE_MONITORING_ENABLED', true),
        
        'log_slow_queries' => [
            'enabled' => env('LOG_SLOW_QUERIES', true),
            'threshold_ms' => env('SLOW_QUERY_THRESHOLD_MS', 500), // 500ms threshold
        ],
        
        'log_high_memory_usage' => [
            'enabled' => env('LOG_HIGH_MEMORY_USAGE', true),
            'threshold_mb' => env('HIGH_MEMORY_THRESHOLD_MB', 128), // 128MB threshold
        ],
        
        'log_many_queries' => [
            'enabled' => env('LOG_MANY_QUERIES', true),
            'threshold' => env('QUERY_COUNT_THRESHOLD', 20), // 20 queries threshold
        ],
    ],

    'caching' => [
        'default_ttl' => env('CACHE_DEFAULT_TTL', 3600), // 1 hour default TTL
        
        'optimization' => [
            'cache_hit_target' => env('CACHE_HIT_TARGET', 80), // 80% target hit rate
            'track_hit_rates' => env('TRACK_CACHE_HIT_RATES', true),
        ],
        
        'strategies' => [
            'user_data_ttl' => env('USER_DATA_CACHE_TTL', 3600), // 1 hour for user data
            'role_permission_ttl' => env('ROLE_PERMISSION_CACHE_TTL', 7200), // 2 hours for roles/permissions
            'configuration_ttl' => env('CONFIG_CACHE_TTL', 86400), // 24 hours for config
        ],
    ],

    'database' => [
        'connection_pool' => [
            'min_connections' => env('DB_MIN_CONNECTIONS', 1),
            'max_connections' => env('DB_MAX_CONNECTIONS', 10),
            'max_idle_time' => env('DB_MAX_IDLE_TIME', 60),
        ],
        
        'query_optimization' => [
            'enable_query_cache' => env('ENABLE_QUERY_CACHE', true),
            'enable_result_cache' => env('ENABLE_RESULT_CACHE', true),
            'n_plus_one_detection' => env('N_PLUS_ONE_DETECTION', true),
        ],
    ],

    'metrics' => [
        'collection' => [
            'enabled' => env('METRICS_COLLECTION_ENABLED', true),
            'storage_driver' => env('METRICS_STORAGE_DRIVER', 'file'), // file, redis, database
        ],
        
        'performance_targets' => [
            'response_time_ms' => env('PERFORMANCE_TARGET_RESPONSE_MS', 200),
            'query_time_ms' => env('PERFORMANCE_TARGET_QUERY_MS', 100),
            'memory_limit_mb' => env('PERFORMANCE_TARGET_MEMORY_MB', 512),
        ],
    ],
];