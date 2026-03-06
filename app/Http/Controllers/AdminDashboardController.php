<?php

namespace App\Http\Controllers;

use App\Models\Book;
use App\Models\Category;
use App\Models\Order;
use App\Models\Review;
use App\Models\User;

class AdminDashboardController extends Controller
{
    public function index()
    {
        $metrics = [
            'users' => User::count(),
            'books' => Book::count(),
            'categories' => Category::count(),
            'orders' => Order::count(),
        ];

        $recentOrders = Order::with('user')->latest()->limit(10)->get();
        $statusSummary = Order::selectRaw('status, COUNT(*) as count')->groupBy('status')->pluck('count', 'status');
        $recentReviews = Review::with(['user', 'book'])->latest()->limit(10)->get();

        return view('admin.dashboard', compact('metrics', 'recentOrders', 'statusSummary', 'recentReviews'));
    }
}
