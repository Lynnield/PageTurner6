<?php

namespace App\Services;

use App\Events\BackupFailed;
use App\Events\BackupSucceeded;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Throwable;

/**
 * BackupService: Manage database and file backups
 */
class BackupService
{
    /**
     * Run full backup
     */
    public static function runBackup(): bool
    {
        try {
            $output = shell_exec('php artisan backup:run 2>&1');
            
            // Get backup file size
            $backupPath = storage_path('app/backups');
            $size = 0;
            
            if (is_dir($backupPath)) {
                foreach (glob($backupPath . '/*') as $file) {
                    $size += filesize($file);
                }
            }

            BackupSucceeded::dispatch($size, $backupPath);
            
            return true;
        } catch (Throwable $e) {
            Log::error('Backup failed', ['error' => $e->getMessage()]);
            BackupFailed::dispatch('Backup process failed: ' . $e->getMessage(), $e);
            
            return false;
        }
    }

    /**
     * Clean old backups
     */
    public static function cleanOldBackups(): int
    {
        try {
            shell_exec('php artisan backup:clean 2>&1');
            return 0;
        } catch (Throwable $e) {
            Log::error('Backup cleanup failed', ['error' => $e->getMessage()]);
            return 1;
        }
    }

    /**
     * Get backup list with metadata
     */
    public static function getBackupList(): array
    {
        $backupPath = storage_path('app/backups');
        $backups = [];

        if (!is_dir($backupPath)) {
            return $backups;
        }

        foreach (glob($backupPath . '/*') as $file) {
            if (is_file($file)) {
                $backups[] = [
                    'name' => basename($file),
                    'path' => $file,
                    'size' => filesize($file),
                    'created' => filemtime($file),
                    'size_readable' => self::formatBytes(filesize($file)),
                ];
            }
        }

        usort($backups, fn($a, $b) => $b['created'] <=> $a['created']);
        return $backups;
    }

    /**
     * Format bytes to human readable
     */
    public static function formatBytes(int $bytes, int $precision = 2): string
    {
        $units = ['B', 'KB', 'MB', 'GB'];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        $bytes /= (1 << (10 * $pow));

        return round($bytes, $precision) . ' ' . $units[$pow];
    }

    /**
     * Get backup disk usage
     */
    public static function getBackupDiskUsage(): array
    {
        $backups = self::getBackupList();
        $totalSize = array_sum(array_column($backups, 'size'));

        return [
            'total_backups' => count($backups),
            'total_size' => $totalSize,
            'total_size_readable' => self::formatBytes($totalSize),
            'backups' => $backups,
        ];
    }
}
