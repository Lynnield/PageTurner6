<?php

namespace App\Providers;

use App\Events\OrderPlaced;
use App\Events\OrderStatusChanged;
use App\Events\ReviewSubmitted;
use App\Models\User;
use App\Notifications\NewOrderForAdmin;
use App\Notifications\OrderPlacedNotification;
use App\Notifications\OrderStatusChangedNotification;
use App\Notifications\ReviewSubmittedNotification;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Failed;
use Illuminate\Auth\Events\Logout;
use Illuminate\Auth\Events\PasswordReset;

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

        Event::listen(OrderPlaced::class, function (OrderPlaced $event) {
            $event->order->user->notify(new OrderPlacedNotification($event->order));
            User::where('role', 'admin')->get()->each->notify(new NewOrderForAdmin($event->order));
        });

        Event::listen(OrderStatusChanged::class, function (OrderStatusChanged $event) {
            $event->order->user->notify(new OrderStatusChangedNotification($event->order));
        });

        Event::listen(ReviewSubmitted::class, function (ReviewSubmitted $event) {
            User::where('role', 'admin')->get()->each->notify(new ReviewSubmittedNotification($event->review));
        });

        $this->registerAuthAuditListeners();
    }

    protected function registerAuthAuditListeners(): void
    {
        Event::listen(Login::class, function (Login $event) {
            $this->logCustomAudit($event->user, 'login', 'Logged in');
        });

        Event::listen(Logout::class, function (Logout $event) {
            if ($event->user) {
                $this->logCustomAudit($event->user, 'logout', 'Logged out');
            }
        });

        Event::listen(Failed::class, function (Failed $event) {
            if ($event->user) {
                $this->logCustomAudit($event->user, 'failed_login', 'Failed login attempt');
            }
        });

        Event::listen(PasswordReset::class, function (PasswordReset $event) {
            $this->logCustomAudit($event->user, 'password_reset', 'Password reset');
        });
    }

    protected function logCustomAudit(?User $user, string $event, string $message): void
    {
        if (! $user) return;

        \App\Models\Audit::create([
            'user_type'      => User::class,
            'user_id'        => $user->id,
            'event'          => $event,
            'auditable_type' => User::class,
            'auditable_id'   => $user->id,
            'old_values'     => [],
            'new_values'     => ['message' => $message],
            'url'            => request()->fullUrl(),
            'ip_address'     => request()->ip(),
            'user_agent'     => request()->userAgent(),
            'http_method'    => request()->method(),
            'request_uuid'   => \Illuminate\Support\Str::uuid(),
            'metadata'       => [],
        ]);
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
