<?php

return [
    /*
    |--------------------------------------------------------------------------
    | API Rate Limiting Configuration
    |--------------------------------------------------------------------------
    */
    'rate_limits' => [
        'public' => [
            'requests' => 30,
            'minutes' => 1,
        ],
        'standard' => [
            'requests' => 60,
            'minutes' => 1,
        ],
        'premium' => [
            'requests' => 300,
            'minutes' => 1,
        ],
        'admin' => [
            'requests' => 1000,
            'minutes' => 1,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Backup Configuration
    |--------------------------------------------------------------------------
    */
    'backup' => [
        'enabled' => env('BACKUP_ENABLED', true),
        'daily_at' => '02:00',
        'retention' => [
            'daily' => 7,
            'weekly' => 4,
            'monthly' => 12,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Import/Export Configuration
    |--------------------------------------------------------------------------
    */
    'import_export' => [
        'chunk_size' => 1000,
        'batch_size' => 1000,
        'max_file_size' => 10240, // 10MB in KB
        'large_export_threshold' => 10000, // Queue exports > 10k rows
        'formats' => ['csv', 'xlsx', 'pdf'],
    ],

    /*
    |--------------------------------------------------------------------------
    | Audit Configuration
    |--------------------------------------------------------------------------
    */
    'audit' => [
        'enabled' => true,
        'archive_after_days' => 365,
        'track_models' => [
            \App\Models\User::class,
            \App\Models\Book::class,
            \App\Models\Order::class,
            \App\Models\Review::class,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Database Optimization
    |--------------------------------------------------------------------------
    */
    'database' => [
        'enable_query_caching' => env('DB_QUERY_CACHING', false),
        'cache_ttl_minutes' => 60,
        'enable_read_write_split' => env('DB_READ_WRITE_SPLIT', false),
    ],

    /*
    |--------------------------------------------------------------------------
    | Notification Configuration
    |--------------------------------------------------------------------------
    */
    'notifications' => [
        'retention_days' => 90,
        'poll_interval_seconds' => 30,
    ],
];
