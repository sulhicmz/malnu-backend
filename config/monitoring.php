<?php

declare(strict_types=1);

return [
    'enabled' => env('MONITORING_ENABLED', true),

    'exclude_paths' => [
        '/health',
        '/metrics',
        '/favicon.ico',
        '/swagger',
    ],

    'alerting' => [
        'enabled' => env('MONITORING_ALERTING_ENABLED', false),
        
        'error_rate_threshold' => env('MONITORING_ERROR_RATE_THRESHOLD', 5),
        
        'response_time_threshold' => env('MONITORING_RESPONSE_TIME_THRESHOLD', 1000),
        
        'disk_usage_threshold' => env('MONITORING_DISK_USAGE_THRESHOLD', 90),
        
        'memory_usage_threshold' => env('MONITORING_MEMORY_USAGE_THRESHOLD', 85),
        
        'channels' => [
            'email' => env('MONITORING_ALERT_EMAIL', null),
            'slack' => env('MONITORING_ALERT_SLACK_WEBHOOK', null),
        ],
    ],

    'metrics' => [
        'retention' => [
            'short_term' => 3600,
            'long_term' => 86400,
        ],

        'response_time_percentiles' => [
            'p50' => true,
            'p90' => true,
            'p95' => true,
            'p99' => true,
        ],

        'collect' => [
            'requests' => true,
            'errors' => true,
            'database' => true,
            'cache' => true,
            'system' => true,
        ],
    ],

    'health_checks' => [
        'database' => [
            'enabled' => env('HEALTH_CHECK_DATABASE', true),
            'timeout' => env('HEALTH_CHECK_DATABASE_TIMEOUT', 5),
        ],

        'redis' => [
            'enabled' => env('HEALTH_CHECK_REDIS', true),
            'timeout' => env('HEALTH_CHECK_REDIS_TIMEOUT', 5),
        ],

        'system' => [
            'enabled' => env('HEALTH_CHECK_SYSTEM', true),
            'disk_usage_threshold' => env('HEALTH_CHECK_DISK_THRESHOLD', 90),
            'load_threshold' => env('HEALTH_CHECK_LOAD_THRESHOLD', 10),
        ],
    ],

    'external_monitoring' => [
        'enabled' => env('EXTERNAL_MONITORING_ENABLED', false),
        
        'apm_services' => [
            'sentry' => [
                'enabled' => env('SENTRY_ENABLED', false),
                'dsn' => env('SENTRY_DSN'),
                'environment' => env('SENTRY_ENVIRONMENT', 'production'),
                'sample_rate' => env('SENTRY_SAMPLE_RATE', 1.0),
            ],

            'new_relic' => [
                'enabled' => env('NEW_RELIC_ENABLED', false),
                'app_name' => env('NEW_RELIC_APP_NAME', 'Malnu Backend'),
                'license_key' => env('NEW_RELIC_LICENSE_KEY'),
            ],

            'datadog' => [
                'enabled' => env('DATADOG_ENABLED', false),
                'api_key' => env('DATADOG_API_KEY'),
                'host' => env('DATADOG_HOST', 'app.datadoghq.com'),
            ],
        ],
    ],

    'database' => [
        'slow_query_threshold' => env('DB_SLOW_QUERY_THRESHOLD', 1000),
        'log_slow_queries' => env('DB_LOG_SLOW_QUERIES', true),
    ],

    'cache' => [
        'track_hit_rate' => true,
        'monitor_memory_usage' => true,
        'alert_on_evictions' => true,
    ],

    'logging' => [
        'enabled' => true,
        'level' => 'info',
        'include_context' => true,
        'exclude_sensitive_data' => true,
    ],

    'performance' => [
        'track_response_times' => true,
        'track_throughput' => true,
        'track_error_rates' => true,
    ],
];
