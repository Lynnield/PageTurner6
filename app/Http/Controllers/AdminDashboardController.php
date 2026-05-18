<?php

namespace App\Http\Controllers;

use App\Models\Audit;
use App\Models\Book;
use App\Models\Category;
use App\Models\ExportLog;
use App\Models\ImportLog;
use App\Models\Order;
use App\Models\Review;
use App\Models\User;
use Illuminate\Support\Facades\DB;

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
        $recentReviews = Review::with(['user', 'book.category'])->latest()->limit(10)->get();

        // Data Management Metrics
        $importStats = [
            'total' => ImportLog::count(),
            'processing' => ImportLog::where('status', 'processing')->count(),
            'failed' => ImportLog::where('status', 'failed')->count(),
            'recent' => ImportLog::latest()->limit(5)->get(),
        ];
        
        $exportStats = [
            'total' => ExportLog::count(),
            'processing' => ExportLog::where('status', 'processing')->count(),
            'recent' => ExportLog::latest()->limit(5)->get(),
        ];

        $auditStats = [
            'recent' => Audit::with('user')->latest()->limit(5)->get(),
        ];

        // System Health (Approximations)
        $systemHealth = [
            'db_size' => $this->getDatabaseSize(),
            'failed_jobs' => DB::table('failed_jobs')->count(),
            'jobs_queue' => DB::table('jobs')->count(),
            'backup_health' => $this->getBackupHealth(),
            'notification_stats' => [
                'unread' => DB::table('notifications')->whereNull('read_at')->count(),
                'total' => DB::table('notifications')->count(),
            ],
            'api_usage' => [
                'total_requests' => Audit::where('url', 'like', 'api/%')->count(),
                'recent_429s' => Audit::where('event', 'rate_limit_exceeded')->where('created_at', '>', now()->subDay())->count(),
            ]
        ];

        return view('admin.dashboard', compact(
            'metrics', 'recentOrders', 'statusSummary', 'recentReviews',
            'importStats', 'exportStats', 'auditStats', 'systemHealth'
        ));
    }

    private function getBackupHealth()
    {
        $lastBackup = DB::table('backup_monitoring')->latest()->first();
        if (!$lastBackup) return 'Unknown';
        
        return $lastBackup->status === 'success' ? 'Healthy' : 'Failing';
    }

    private function getDatabaseSize()
    {
        try {
            $dbName = env('DB_DATABASE');
            if (env('DB_CONNECTION') === 'mysql') {
                $size = DB::select("SELECT SUM(data_length + index_length) / 1024 / 1024 AS size FROM information_schema.tables WHERE table_schema = ?", [$dbName]);
                return round($size[0]->size ?? 0, 2) . ' MB';
            }
            return 'N/A';
        } catch (\Exception $e) {
            return 'N/A';
        }
    }
}
