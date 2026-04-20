<?php

use App\Models\Book;
use App\Models\Category;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Http;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('translate:content {--from=auto} {--to=en}', function () {
    $from = $this->option('from');
    $to = $this->option('to');
    $endpoint = env('LIBRETRANSLATE_URL', 'https://libretranslate.com/translate');
    $apiKey = env('LIBRETRANSLATE_API_KEY');

    $translate = function ($text) use ($endpoint, $apiKey, $from, $to) {
        if (! $text) {
            return $text;
        }
        try {
            if (class_exists(\Illuminate\Support\Facades\Http::class)) {
                $response = Http::timeout(10)->asJson()->post($endpoint, [
                    'q' => $text,
                    'source' => $from,
                    'target' => $to,
                    'format' => 'text',
                    'api_key' => $apiKey ?: null,
                ]);
                if ($response->ok()) {
                    $data = $response->json();

                    return $data['translatedText'] ?? $text;
                }
            }
        } catch (\Throwable $e) {
        }

        return $text;
    };

    $this->info('Translating categories...');
    Category::chunk(50, function ($chunk) use ($translate) {
        foreach ($chunk as $category) {
            $name = $category->name_en ?: $translate($category->name);
            $desc = $category->description_en ?: $translate($category->description);
            $category->update([
                'name_en' => $name,
                'description_en' => $desc,
            ]);
        }
    });

    $this->info('Translating books...');
    Book::chunk(50, function ($chunk) use ($translate) {
        foreach ($chunk as $book) {
            $title = $book->title_en ?: $translate($book->title);
            $desc = $book->description_en ?: $translate($book->description);
            $book->update([
                'title_en' => $title,
                'description_en' => $desc,
            ]);
        }
    });

    $this->info('Done.');
})->purpose('Translate categories and books into English and store results');

// Scheduled maintenance
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schedule;

Artisan::command('order:cleanup-pending', function () {
    $count = \App\Models\Order::where('status', 'pending')
        ->where('created_at', '<', now()->subHours(24))
        ->update(['status' => 'cancelled']);
    $this->info("Cancelled {$count} pending orders.");
})->purpose('Cancel pending orders > 24 hours old');

Artisan::command('session:cleanup', function () {
    $driver = config('session.driver');
    if ($driver === 'database') {
        $table = config('session.table', 'sessions');
        $lifetime = config('session.lifetime', 120);
        $count = \Illuminate\Support\Facades\DB::table($table)
            ->where('last_activity', '<', now()->subMinutes($lifetime)->getTimestamp())
            ->delete();
        $this->info("Cleared {$count} expired sessions.");
    } else {
        $this->info("Session driver is not database, skipped.");
    }
})->purpose('Clear expired sessions');

Artisan::command('log:rotate', function () {
    $logPath = storage_path('logs/laravel.log');
    if (file_exists($logPath)) {
        $archivePath = storage_path('logs/laravel_' . now()->format('Y_m_d_His') . '.log.gz');
        $gz = gzopen($archivePath, 'w9');
        gzwrite($gz, file_get_contents($logPath));
        gzclose($gz);
        file_put_contents($logPath, '');
        $this->info("Logs archived to {$archivePath}");
    } else {
        $this->info("No log file found.");
    }
})->purpose('Archive and compress old logs');

Artisan::command('report:generate-daily', function () {
    $date = now()->subDay()->format('Y-m-d');
    $export = new \App\Exports\OrderExport(['date_from' => $date, 'date_to' => $date], 'financial');
    
    $filename = "exports/financial/daily_sales_{$date}.xlsx";
    \Maatwebsite\Excel\Facades\Excel::store($export, $filename, 'local');
    
    $admins = \App\Models\User::where('role', 'admin')->get();
    foreach ($admins as $admin) {
        \Illuminate\Support\Facades\Mail::to($admin->email)
            ->send(new \App\Mail\DailySalesReport(storage_path("app/private/{$filename}"), $date));
    }
    $this->info("Daily report generated and emailed.");
})->purpose('Generate daily sales report');

Artisan::command('notification:prune', function () {
    $count = \Illuminate\Support\Facades\DB::table('notifications')
        ->where('created_at', '<', now()->subDays(90))
        ->delete();
    $this->info("Deleted {$count} old notification records.");
})->purpose('Delete old notification records > 90 days');

Artisan::command('audit:archive', function () {
    $cutoff = now()->subYear();
    
    // Copy to archive table
    $query = \Illuminate\Support\Facades\DB::table('audits')->where('created_at', '<', $cutoff);
    $records = $query->get()->map(fn ($row) => (array) $row)->toArray();
    
    if (count($records) > 0) {
        foreach (array_chunk($records, 500) as $chunk) {
            \Illuminate\Support\Facades\DB::table('audits_archive')->insert($chunk);
        }
        $query->delete();
        $this->info("Archived " . count($records) . " audit logs.");
    } else {
        $this->info("No audit logs to archive.");
    }
})->purpose('Archive audit logs > 1 year old');

$scheduleTask = function ($command, $frequency, $time = null) {
    $event = Schedule::command($command);
    if ($time) {
        $event->$frequency($time);
    } else {
        $event->$frequency();
    }
    
    $event->withoutOverlapping()
        ->onSuccess(function () use ($command) {
            Log::info("Scheduled task succeeded: {$command}");
        })
        ->onFailure(function () use ($command) {
            Log::error("Scheduled task failed: {$command}");
        });
};

$scheduleTask('backup:run', 'dailyAt', '02:00');
$scheduleTask('backup:clean', 'dailyAt', '03:00');
$scheduleTask('order:cleanup-pending', 'hourly');
$scheduleTask('session:cleanup', 'daily');
$scheduleTask('log:rotate', 'weekly');
$scheduleTask('report:generate-daily', 'dailyAt', '06:00');
$scheduleTask('notification:prune', 'weekly');
$scheduleTask('audit:archive', 'monthly');
