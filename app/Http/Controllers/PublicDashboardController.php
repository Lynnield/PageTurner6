<?php

namespace App\Http\Controllers;

use App\Models\Book;
use App\Models\Category;
use App\Models\Order;

class PublicDashboardController extends Controller
{
    public function index()
    {
        $metrics = [
            'books' => Book::count(),
            'categories' => Category::count(),
            'completed_orders' => Order::where('status', 'completed')->count(),
        ];

        $latestBooks = Book::with('category')->latest()->limit(8)->get();

        return view('dashboard.public', compact('metrics', 'latestBooks'));
    }
}
