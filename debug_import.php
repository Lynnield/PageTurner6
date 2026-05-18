<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\ImportLog;
use App\Models\ImportLogFailure;
use Illuminate\Support\Facades\DB;

$log = ImportLog::latest()->first();
if ($log) {
    echo "Latest Log ID: {$log->id}\n";
    echo "Status: {$log->status}\n";
    echo "Total Rows (Log): {$log->total_rows}\n";
    echo "Processed: {$log->processed_rows}\n";
    echo "Success: {$log->success_rows}\n";
    echo "Failed: {$log->failed_rows}\n";
    echo "Error Message: {$log->error_message}\n\n";

    $failures = ImportLogFailure::where('import_log_id', $log->id)->take(5)->get();
    echo "First 5 Failures for this Log:\n";
    foreach ($failures as $f) {
        echo "Row: {$f->row_number}, Attribute: {$f->attribute}\n";
        echo "Errors: " . json_encode($f->errors) . "\n";
        echo "Values: " . json_encode($f->values) . "\n\n";
    }
} else {
    echo "No import logs found.\n";
}

$bookCount = DB::table('books')->count();
echo "Current Total Books in DB: {$bookCount}\n";
