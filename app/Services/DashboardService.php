<?php

namespace App\Services;

use App\Models\ExportLog;
use App\Models\ImportLog;
use Illuminate\Support\Facades\DB;

/**
 * DashboardService: Provide metrics for admin dashboard
 */
class DashboardService
{
    /**
     * Get system health metrics
     */
    public static function getSystemHealth(): array
    {
        return [
            'database_connected' => self::isDatabaseConnected(),
            'queue_working' => self::isQueueWorking(),
            'cache_working' => self::isCacheWorking(),
            'disk_space' => self::getDiskSpace(),
            'memory_usage' => self::getMemoryUsage(),
        ];
    }

    /**
     * Get import/export status
     */
    public static function getImportExportStatus(): array
    {
        return [
            'pending_imports' => ImportLog::where('status', 'queued')->count(),
            'pending_exports' => ExportLog::where('status', 'queued')->count(),
            'failed_imports' => ImportLog::where('status', 'failed')->where('created_at', '>=', now()->subDays(7))->count(),
            'failed_exports' => ExportLog::where('status', 'failed')->where('created_at', '>=', now()->subDays(7))->count(),
            'recent_imports' => ImportLog::latest()->limit(5)->get(),
            'recent_exports' => ExportLog::latest()->limit(5)->get(),
        ];
    }

    /**
     * Get queue health
     */
    public static function getQueueHealth(): array
    {
        return [
            'pending_jobs' => DB::table('jobs')->count(),
            'failed_jobs' => DB::table('failed_jobs')->count(),
        ];
    }

    /**
     * Get API usage statistics
     */
    public static function getApiUsageStats(): array
    {
        return [
            'total_requests_today' => cache()->get('api_requests_today', 0),
            'total_requests_month' => cache()->get('api_requests_month', 0),
            'failed_requests_today' => cache()->get('api_failures_today', 0),
        ];
    }

    /**
     * Get audit summary
     */
    public static function getAuditSummary(): array
    {
        return [
            'logins_today' => DB::table('audits')
                ->where('action', 'login')
                ->whereDate('created_at', today())
                ->count(),
            'failed_logins_today' => DB::table('audits')
                ->where('action', 'failed_login')
                ->whereDate('created_at', today())
                ->count(),
            'critical_events' => DB::table('audits')
                ->where('action', 'security_event')
                ->where('created_at', '>=', now()->subHours(24))
                ->count(),
        ];
    }

    /**
     * Check database connection
     */
    private static function isDatabaseConnected(): bool
    {
        try {
            DB::connection()->getPdo();
            return true;
        } catch (\Throwable $e) {
            return false;
        }
    }

    /**
     * Check queue is working
     */
    private static function isQueueWorking(): bool
    {
        try {
            $lastJob = DB::table('jobs')->latest()->first();
            return true; // If we can query, queue infrastructure exists
        } catch (\Throwable $e) {
            return false;
        }
    }

    /**
     * Check cache is working
     */
    private static function isCacheWorking(): bool
    {
        try {
            cache()->put('health_check', true, 1);
            return cache()->get('health_check') === true;
        } catch (\Throwable $e) {
            return false;
        }
    }

    /**
     * Get disk space info
     */
    private static function getDiskSpace(): array
    {
        $path = storage_path();
        $total = disk_total_space($path);
        $free = disk_free_space($path);
        $used = $total - $free;

        return [
            'total' => $total,
            'used' => $used,
            'free' => $free,
            'percent_used' => round(($used / $total) * 100, 2),
        ];
    }

    /**
     * Get memory usage info
     */
    private static function getMemoryUsage(): array
    {
        $usage = memory_get_usage(true);
        $peak = memory_get_peak_usage(true);
        $limit = (int) ini_get('memory_limit') * 1024 * 1024;

        return [
            'current' => $usage,
            'peak' => $peak,
            'limit' => $limit,
            'percent_used' => round(($usage / $limit) * 100, 2),
        ];
    }
}
