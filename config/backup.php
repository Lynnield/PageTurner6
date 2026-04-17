<?php

$config = require base_path('vendor/spatie/laravel-backup/config/backup.php');

// Enterprise policy: only include required paths, encrypt archives, and retain per spec.
$config['backup']['source']['files']['include'] = [
    // uploaded covers
    storage_path('app/public'),
    // key config (no .env)
    base_path('config'),
];

$config['backup']['source']['files']['exclude'] = array_values(array_unique(array_merge(
    $config['backup']['source']['files']['exclude'] ?? [],
    [
        base_path('vendor'),
        base_path('node_modules'),
        storage_path('framework'),
        base_path('.git'),
    ]
)));

// Keep DB + files.
$config['backup']['source']['databases'] = [env('DB_CONNECTION', 'mysql')];

// Encrypted local backup is mandatory.
$config['backup']['password'] = env('BACKUP_ARCHIVE_PASSWORD');
$config['backup']['encryption'] = env('BACKUP_ARCHIVE_ENCRYPTION', 'default');

// Destination disks: local + optional S3-compatible.
$config['backup']['destination']['disks'] = array_values(array_filter([
    'local',
    env('BACKUP_S3_DISK') ?: null,
]));

// Retention policy:
// 7 daily, 4 weekly, 12 monthly (monthly ≈ 12 months, weekly ≈ 4 weeks).
$config['cleanup']['default_strategy'] = [
    'keep_all_backups_for_days' => 7,
    'keep_daily_backups_for_days' => 7,
    'keep_weekly_backups_for_weeks' => 4,
    'keep_monthly_backups_for_months' => 12,
    'keep_yearly_backups_for_years' => 2,
    'delete_oldest_backups_when_using_more_megabytes_than' => null,
];

// Notifications: configure recipient via env.
$config['notifications']['mail']['to'] = env('BACKUP_ALERT_EMAIL', 'admin@example.com');

return $config;

