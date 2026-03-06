<?php

namespace App\Http\Controllers;

use App\Models\TwoFactorSecret;

class CustomerDashboardController extends Controller
{
    public function index()
    {
        $user = auth()->user();

        $orders = $user->orders()->latest()->limit(10)->get();
        $orderCount = $user->orders()->count();
        $statusSummary = $user->orders()->selectRaw('status, COUNT(*) as count')->groupBy('status')->pluck('count', 'status');
        $purchasedBookIds = $user->orders()
            ->where('status', 'completed')
            ->with('items')
            ->get()
            ->flatMap->items
            ->pluck('book_id')
            ->unique()
            ->take(10);
        $reviews = $user->reviews()->latest()->limit(10)->with('book')->get();

        $emailVerified = ! is_null($user->email_verified_at);
        $twoFactorEnabled = TwoFactorSecret::where('user_id', $user->id)->whereNotNull('enabled_at')->exists();

        return view('dashboard.customer', compact(
            'user',
            'orders',
            'orderCount',
            'statusSummary',
            'purchasedBookIds',
            'reviews',
            'emailVerified',
            'twoFactorEnabled'
        ));
    }
}
