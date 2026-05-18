<?php

namespace App\Services;

use Illuminate\Cache\RateLimitManager;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\RateLimiter;

/**
 * RateLimiterService: Manage API rate limiting tiers
 */
class RateLimiterService
{
    const TIERS = [
        'public' => ['requests' => 30, 'minutes' => 1],
        'standard' => ['requests' => 60, 'minutes' => 1],
        'premium' => ['requests' => 300, 'minutes' => 1],
        'admin' => ['requests' => 1000, 'minutes' => 1],
    ];

    /**
     * Get rate limit for user tier
     */
    public static function getLimitForUser($user): array
    {
        if ($user?->isAdmin()) {
            return self::TIERS['admin'];
        }

        $tier = $user?->api_tier ?? 'standard';
        return self::TIERS[$tier] ?? self::TIERS['standard'];
    }

    /**
     * Check if user has exceeded rate limit
     */
    public static function isLimited($identifier): bool
    {
        return RateLimiter::tooManyAttempts($identifier, $this->getLimit($identifier));
    }

    /**
     * Get remaining requests for identifier
     */
    public static function getRemaining($identifier): int
    {
        $limit = self::TIERS['standard']['requests'];
        $attempts = RateLimiter::attempts($identifier);
        
        return max(0, $limit - $attempts);
    }

    /**
     * Get seconds until limit resets
     */
    public static function getRetryAfter($identifier): int
    {
        return RateLimiter::availableIn($identifier);
    }

    /**
     * Record a request attempt
     */
    public static function attempt($identifier): int
    {
        return RateLimiter::hit($identifier, 60);
    }
}
