<?php

namespace App\Http\Controllers;

use App\Models\TwoFactorSecret;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;

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

    public function exportData()
    {
        $user = auth()->user();
        $data = [
            'profile' => $user->only(['id', 'name', 'email', 'created_at', 'updated_at']),
            'orders' => $user->orders()->with('items.book')->get()->toArray(),
            'reviews' => $user->reviews()->with('book')->get()->toArray(),
        ];

        return Response::json($data, 200, [
            'Content-Disposition' => 'attachment; filename="personal_data.json"',
        ]);
    }

    public function exportOrders()
    {
        $user = auth()->user();
        $export = new \App\Exports\OrderExport(['user_id' => $user->id], 'customer');
        $filename = "my_orders_" . now()->format('Y_m_d') . ".xlsx";
        
        return \Maatwebsite\Excel\Facades\Excel::download($export, $filename);
    }

    public function exportReadingHistory()
    {
        $user = auth()->user();
        
        // Simulating reading history via completed orders + reviews
        $books = \App\Models\Book::whereHas('orderItems.order', function ($q) use ($user) {
            $q->where('user_id', $user->id)->where('status', 'completed');
        })->orWhereHas('reviews', function ($q) use ($user) {
            $q->where('user_id', $user->id);
        })->get();

        $headers = [
            'Content-type'        => 'text/csv',
            'Content-Disposition' => 'attachment; filename="reading_history.csv"',
            'Pragma'              => 'no-cache',
            'Cache-Control'       => 'must-revalidate, post-check=0, pre-check=0',
            'Expires'             => '0'
        ];

        $callback = function () use ($books) {
            $file = fopen('php://output', 'w');
            fputcsv($file, ['Book ID', 'ISBN', 'Title', 'Author', 'Category']);
            foreach ($books as $book) {
                fputcsv($file, [$book->id, $book->isbn, $book->title, $book->author, $book->category?->name]);
            }
            fclose($file);
        };

        return Response::stream($callback, 200, $headers);
    }
}
