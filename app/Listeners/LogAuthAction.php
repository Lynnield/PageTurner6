<?php

namespace App\Listeners;

use App\Models\Audit;
use App\Models\User;
use Illuminate\Auth\Events\Failed;
use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Logout;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Str;

class LogAuthAction implements ShouldQueue
{
    use InteractsWithQueue;

    /**
     * Handle the event.
     */
    public function handle(mixed $event): void
    {
        $user = $event->user ?? null;
        if (!$user && isset($event->credentials['email'])) {
             // For failed login, we might not have a user object if email was wrong
             $user = User::where('email', $event->credentials['email'])->first();
        }

        if (!$user) return;

        $action = match (true) {
            $event instanceof Login => 'login',
            $event instanceof Logout => 'logout',
            $event instanceof Failed => 'failed_login',
            $event instanceof PasswordReset => 'password_reset',
            default => 'auth_event',
        };

        $message = match (true) {
            $event instanceof Login => 'Logged in',
            $event instanceof Logout => 'Logged out',
            $event instanceof Failed => 'Failed login attempt',
            $event instanceof PasswordReset => 'Password reset',
            default => 'Auth event occurred',
        };

        Audit::create([
            'user_type'      => User::class,
            'user_id'        => $user->id,
            'event'          => $action,
            'auditable_type' => User::class,
            'auditable_id'   => $user->id,
            'old_values'     => [],
            'new_values'     => ['message' => $message],
            'url'            => request()->fullUrl(),
            'ip_address'     => request()->ip(),
            'user_agent'     => request()->userAgent(),
            'http_method'    => request()->method(),
            'request_uuid'   => Str::uuid(),
            'metadata'       => [
                'ip' => request()->ip(),
                'user_agent' => request()->userAgent(),
            ],
        ]);
    }
}
