<?php

declare(strict_types=1);

return [
    /*
    |--------------------------------------------------------------------------
    | Backup Manager Configuration
    |--------------------------------------------------------------------------
    |
    | This file contains the configuration options for the backup system.
    | You can customize the backup locations, retention policies, and other settings.
    |
    */

    'backup' => [
        // Default backup disk/directory
        'default_disk' => 'local',
        
        // Backup directories configuration
        'directories' => [
            'database' => storage_path('backups/database'),
            'filesystem' => storage_path('backups/filesystem'),
            'config' => storage_path('backups/config'),
            'comprehensive' => storage_path('backups'),
        ],
        
        // Default retention policy - keep last N backups
        'retention' => [
            'database' => 7,      // Keep 7 days of database backups
            'filesystem' => 7,    // Keep 7 days of filesystem backups
            'config' => 5,        // Keep 5 days of config backups
            'comprehensive' => 5, // Keep 5 days of comprehensive backups
        ],
        
        // Default backup options
        'options' => [
            'compress' => true,
            'verify' => true,
            'encrypt' => false,
            'password' => env('BACKUP_PASSWORD', null),
        ],
        
        // Schedule configuration
        'schedule' => [
            'database' => '0 2 * * *',        // Daily at 2 AM
            'comprehensive' => '0 3 * * 0',   // Weekly on Sunday at 3 AM
            'verification' => '0 4 * * *',    // Daily at 4 AM
            'monitoring' => '0 5 * * *',      // Daily at 5 AM
        ],
        
        // Alert configuration
        'alerts' => [
            'email' => env('BACKUP_ALERT_EMAIL', null),
            'webhook' => env('BACKUP_WEBHOOK_URL', null),
            'slack_webhook_url' => env('BACKUP_SLACK_WEBHOOK_URL', null),
        ],
        
        // Filesystem backup configuration
        'filesystem' => [
            'include' => [
                BASE_PATH . '/app',
                BASE_PATH . '/config',
                BASE_PATH . '/database',
                BASE_PATH . '/resources',
                BASE_PATH . '/tests',
            ],
            'exclude' => [
                BASE_PATH . '/node_modules',
                BASE_PATH . '/vendor',
                BASE_PATH . '/.git',
                BASE_PATH . '/.idea',
                BASE_PATH . '/.vscode',
                BASE_PATH . '/storage/logs',
                BASE_PATH . '/storage/framework/cache',
                BASE_PATH . '/storage/temp',
            ],
        ],
        
        // Database backup configuration
        'database' => [
            'connections' => [
                'mysql' => [
                    'driver' => 'mysql',
                    'dump_command_path' => env('MYSQL_DUMP_PATH', '/usr/bin/mysqldump'),
                    'restore_command_path' => env('MYSQL_RESTORE_PATH', '/usr/bin/mysql'),
                ],
                'sqlite' => [
                    'driver' => 'sqlite',
                    'copy_only' => true,
                ],
            ],
        ],
        
        // Cloud storage configuration (for offsite backup)
        'cloud' => [
            'enabled' => env('CLOUD_BACKUP_ENABLED', false),
            'provider' => env('CLOUD_BACKUP_PROVIDER', 's3'), // s3, google, azure, etc.
            'bucket' => env('CLOUD_BACKUP_BUCKET', ''),
            'region' => env('CLOUD_BACKUP_REGION', ''),
            'credentials' => [
                'key' => env('CLOUD_BACKUP_KEY', ''),
                'secret' => env('CLOUD_BACKUP_SECRET', ''),
            ],
        ],
    ],
];