<?php

namespace App\Providers;

use App\Models\User;
use App\Models\Book;
use App\Observers\BookObserver;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
use Illuminate\Database\Eloquent\Model;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void {}

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->configureRateLimiting();

        // Part 5: Prevent lazy loading globally
        Model::preventLazyLoading(! app()->isProduction());

        // Part 10: Register Book Observer
        Book::observe(BookObserver::class);
    }

    protected function configureRateLimiting(): void
    {
        // Public API / General Browsing (30 req/min, 0.5/sec)
        RateLimiter::for('public', function (Request $request) {
            return Limit::perMinute(30)->by($request->ip())
                ->response(fn () => $this->rateLimitResponse(30));
        });

        // Authenticated API / Regular Customers (60 req/min, 1/sec)
        RateLimiter::for('standard', function (Request $request) {
            return Limit::perMinute(60)->by($request->user()?->id ?: $request->ip())
                ->response(fn () => $this->rateLimitResponse(60));
        });

        // Premium/VIP API Access (300 req/min, 5/sec)
        RateLimiter::for('premium', function (Request $request) {
            return Limit::perMinute(300)->by($request->user()?->id ?: $request->ip())
                ->response(fn () => $this->rateLimitResponse(300));
        });

        // Administrative Operations (1000 req/min, ~16/sec)
        RateLimiter::for('admin', function (Request $request) {
            return Limit::perMinute(1000)->by($request->user()?->id ?: $request->ip())
                ->response(fn () => $this->rateLimitResponse(1000));
        });

        // Authentication operations (Login, Reg, Password Reset - 10 req/min)
        RateLimiter::for('auth', function (Request $request) {
            return Limit::perMinute(10)->by($request->ip())
                ->response(fn () => $this->rateLimitResponse(10));
        });

        // Main API entry point routing
        RateLimiter::for('api', function (Request $request) {
            $user = $request->user();
            
            // Bypass for trusted IPs
            if (in_array($request->ip(), explode(',', env('TRUSTED_IPS', '127.0.0.1')))) {
                return Limit::none();
            }

            if ($user) {
                if ($user->isAdmin()) {
                    return Limit::perMinute(1000)->by($user->id)->response(fn () => $this->rateLimitResponse(1000));
                }
                
                return match ($user->api_tier ?? 'standard') {
                    'premium' => Limit::perMinute(300)->by($user->id)->response(fn () => $this->rateLimitResponse(300)),
                    default   => Limit::perMinute(60)->by($user->id)->response(fn () => $this->rateLimitResponse(60)),
                };
            }

            return Limit::perMinute(30)->by($request->ip())->response(fn () => $this->rateLimitResponse(30));
        });
    }

    private function rateLimitResponse(int $limit)
    {
        return response()->json([
            'error' => 'Too Many Requests',
            'message' => 'Rate limit exceeded.',
            'limit' => $limit
        ], 429);
    }
}
