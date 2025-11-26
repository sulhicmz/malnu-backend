<?php

declare(strict_types=1);

return [
    /*
    |--------------------------------------------------------------------------
    | Backup Settings
    |--------------------------------------------------------------------------
    |
    | Configuration for the backup and disaster recovery system.
    |
    */

    // Backup storage settings
    'storage' => [
        'local' => [
            'path' => BASE_PATH . '/storage/backups',
            'visibility' => 'private',
        ],
        'cloud' => [
            // Cloud storage settings for remote backup (S3, etc.)
            'enabled' => false,
            'provider' => 's3', // s3, gcs, azure, etc.
            'bucket' => env('BACKUP_CLOUD_BUCKET', ''),
            'region' => env('BACKUP_CLOUD_REGION', ''),
        ],
    ],

    // Backup retention policy
    'retention' => [
        'local_days' => (int)env('BACKUP_LOCAL_RETENTION_DAYS', 30),
        'cloud_days' => (int)env('BACKUP_CLOUD_RETENTION_DAYS', 90),
    ],

    // Backup schedule
    'schedule' => [
        'enabled' => true,
        'frequency' => env('BACKUP_FREQUENCY', 'daily'), // daily, weekly, monthly
        'time' => env('BACKUP_TIME', '02:00'), // 24-hour format
    ],

    // Encryption settings
    'encryption' => [
        'enabled' => (bool)env('BACKUP_ENCRYPTION_ENABLED', false),
        'key' => env('BACKUP_ENCRYPTION_KEY', ''),
        'algorithm' => env('BACKUP_ENCRYPTION_ALGORITHM', 'AES-256-CBC'),
    ],

    // Notification settings
    'notifications' => [
        'on_success' => (bool)env('BACKUP_NOTIFY_ON_SUCCESS', false),
        'on_failure' => (bool)env('BACKUP_NOTIFY_ON_FAILURE', true),
        'channels' => [
            'mail' => (bool)env('BACKUP_NOTIFY_MAIL', false),
            'slack' => (bool)env('BACKUP_NOTIFY_SLACK', false),
        ],
    ],

    // Database backup settings
    'database' => [
        'include_migrations' => true,
        'exclude_tables' => [
            // Add tables to exclude from backup if needed
        ],
        'timeout' => 300, // seconds
    ],

    // File backup settings
    'files' => [
        'include' => [
            BASE_PATH . '/app',
            BASE_PATH . '/config',
            BASE_PATH . '/resources',
            BASE_PATH . '/database/migrations',
        ],
        'exclude' => [
            BASE_PATH . '/storage/backups',
            BASE_PATH . '/node_modules',
            BASE_PATH . '/vendor',
            BASE_PATH . '/.git',
        ],
        'timeout' => 600, // seconds
    ],
];