<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;

/**
 * DatabaseOptimizationService: Manage query optimization and caching
 */
class DatabaseOptimizationService
{
    /**
     * Add query cache wrapper
     */
    public static function withCache($key, $minutes, callable $callback)
    {
        return cache()->remember($key, now()->addMinutes($minutes), $callback);
    }

    /**
     * Prevent N+1 queries - eager load relationships
     */
    public static function preventN1($models, $relations): \Illuminate\Database\Eloquent\Collection
    {
        return $models->load($relations);
    }

    /**
     * Get database query statistics
     */
    public static function getQueryStats(): array
    {
        $queries = DB::getQueryLog();
        
        return [
            'total_queries' => count($queries),
            'total_time' => collect($queries)->sum('time'),
            'average_time' => count($queries) > 0 
                ? collect($queries)->sum('time') / count($queries)
                : 0,
            'slow_queries' => collect($queries)
                ->filter(fn($q) => $q['time'] > 100)
                ->count(),
        ];
    }

    /**
     * Add database index if it doesn't exist
     */
    public static function ensureIndexExists(string $table, string $column, string $indexName = null): bool
    {
        $indexName = $indexName ?? "{$table}_{$column}_index";
        
        try {
            DB::statement("ALTER TABLE {$table} ADD INDEX {$indexName} ({$column})");
            return true;
        } catch (\Throwable $e) {
            // Index may already exist
            return false;
        }
    }

    /**
     * Enable query logging for debugging
     */
    public static function enableQueryLogging(): void
    {
        DB::enableQueryLog();
    }

    /**
     * Get slow queries
     */
    public static function getSlowQueries(int $thresholdMs = 100): array
    {
        return collect(DB::getQueryLog())
            ->filter(fn($q) => $q['time'] > $thresholdMs)
            ->sortByDesc('time')
            ->values()
            ->toArray();
    }
}
