<?php

namespace App\Http\Middleware;

use App\Models\TwoFactorSecret;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureTwoFactorVerified
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user) {
            $enabled = TwoFactorSecret::where('user_id', $user->id)->whereNotNull('enabled_at')->exists();
            if ($enabled && ! $request->session()->get('2fa_passed')) {
                if (! $request->routeIs('two-factor.*')) {
                    return redirect()->route('two-factor.challenge');
                }
            }
        }

        return $next($request);
    }
}
