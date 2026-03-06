<?php

namespace App\Http\Controllers;

use App\Models\TwoFactorCode;
use App\Models\TwoFactorSecret;
use App\Notifications\TwoFactorDisabled;
use App\Notifications\TwoFactorEnabled;
use App\Notifications\TwoFactorLoginCode;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class TwoFactorController extends Controller
{
    public function settings()
    {
        $user = auth()->user();
        $secret = TwoFactorSecret::firstOrNew(['user_id' => $user->id]);

        return view('security.two-factor', compact('secret'));
    }

    public function enableEmail(Request $request)
    {
        $user = $request->user();
        $secret = TwoFactorSecret::updateOrCreate(
            ['user_id' => $user->id],
            ['method' => 'email_otp', 'enabled_at' => now()]
        );

        $user->notify(new TwoFactorEnabled('email_otp'));

        return back()->with('success', 'Two-factor authentication enabled.');
    }

    public function disable(Request $request)
    {
        $user = $request->user();
        TwoFactorSecret::where('user_id', $user->id)->delete();
        $user->notify(new TwoFactorDisabled);

        return back()->with('success', 'Two-factor authentication disabled.');
    }

    public function challenge()
    {
        $user = auth()->user();
        if (! $user) {
            return redirect()->route('login');
        }
        $this->sendLoginCode($user);

        return view('auth.two-factor-challenge');
    }

    public function verify(Request $request)
    {
        $request->validate([
            'code' => ['required', 'digits:6'],
        ]);

        $user = $request->user();
        $record = TwoFactorCode::where('user_id', $user->id)->latest()->first();
        if (! $record || $record->expires_at->isPast() || ! Hash::check($request->code, $record->code_hash)) {
            return back()->withErrors(['code' => 'Invalid or expired code.']);
        }

        $request->session()->put('2fa_passed', true);
        TwoFactorCode::where('user_id', $user->id)->delete();

        return redirect()->intended(route('dashboard', absolute: false));
    }

    protected function sendLoginCode($user): void
    {
        $code = (string) random_int(100000, 999999);
        TwoFactorCode::updateOrCreate(
            ['user_id' => $user->id],
            ['code_hash' => Hash::make($code), 'expires_at' => now()->addMinutes(10)]
        );
        $user->notify(new TwoFactorLoginCode($code));
    }
}
