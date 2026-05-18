<?php

namespace App\Http\Middleware;

use App\Services\RateLimiterService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * ApiRateLimitMiddleware: Enforce tiered rate limiting on API endpoints
 * Public: 30/min, Standard: 60/min, Premium: 300/min, Admin: 1000/min
 */
class ApiRateLimitMiddleware
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();
        $identifier = $user?->id ?? $request->ip();

        // Get tier-specific limit
        $limit = $user 
            ? RateLimiterService::getLimitForUser($user)
            : RateLimiterService::TIERS['public'];

        $key = "api_rate_limit_{$identifier}";

        // Check if rate limited
        if ($this->isLimited($key, $limit)) {
            return response()->json([
                'message' => 'Too many requests. Please try again later.',
                'retry_after' => $this->getRetryAfter($key),
            ], 429)
            ->header('Retry-After', $this->getRetryAfter($key))
            ->header('X-RateLimit-Limit', $limit['requests'])
            ->header('X-RateLimit-Remaining', 0);
        }

        // Record attempt
        $attempts = $this->recordAttempt($key, $limit);

        // Add rate limit headers to response
        $response = $next($request);

        return $response
            ->header('X-RateLimit-Limit', $limit['requests'])
            ->header('X-RateLimit-Remaining', max(0, $limit['requests'] - $attempts))
            ->header('X-RateLimit-Reset', now()->addMinutes($limit['minutes'])->timestamp);
    }

    /**
     * Check if request has exceeded rate limit
     */
    private function isLimited(string $key, array $limit): bool
    {
        $attempts = cache()->get($key, 0);
        return $attempts >= $limit['requests'];
    }

    /**
     * Record a request attempt
     */
    private function recordAttempt(string $key, array $limit): int
    {
        $attempts = cache()->get($key, 0);
        $newAttempts = $attempts + 1;

        cache()->put(
            $key,
            $newAttempts,
            now()->addMinutes($limit['minutes'])
        );

        return $newAttempts;
    }

    /**
     * Get seconds until limit resets
     */
    private function getRetryAfter(string $key): int
    {
        $ttl = cache()->getStore()->connection()->ttl($key);
        return max(1, $ttl);
    }
}
