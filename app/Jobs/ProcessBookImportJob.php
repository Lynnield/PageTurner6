<?php

namespace App\Jobs;

use App\Events\ImportCompleted;
use App\Imports\BookImport;
use App\Models\ImportLog;
use App\Services\AuditService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;
use Throwable;

/**
 * ProcessBookImportJob: Queue large book import operations
 * Handles validation, chunking, and progress tracking
 */
class ProcessBookImportJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 3;
    public $timeout = 3600;

    /**
     * Create a new job instance.
     */
    public function __construct(
        private ImportLog $importLog
    ) {
        $this->onQueue('imports');
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            $this->importLog->update(['status' => 'processing']);

            $import = new BookImport($this->importLog->id, $this->importLog->mode);

            // Process the import
            Excel::import(
                $import,
                $this->importLog->stored_path,
                $this->importLog->file_disk
            );

            // Get results
            $processed = $import->getProcessedCount();
            $failed = $import->getFailedCount();

            $this->importLog->update([
                'status' => 'completed',
                'total_rows' => $processed + $failed,
                'failed_rows' => $failed,
                'finished_at' => now(),
            ]);

            // Log to audit trail
            AuditService::logImport($this->importLog->id, $processed, $failed);

            // Dispatch completion event
            ImportCompleted::dispatch($this->importLog);

            Log::info("Book import completed", [
                'import_log_id' => $this->importLog->id,
                'processed' => $processed,
                'failed' => $failed,
            ]);

        } catch (Throwable $e) {
            $this->importLog->update([
                'status' => 'failed',
                'error_message' => $e->getMessage(),
                'finished_at' => now(),
            ]);

            Log::error("Book import failed", [
                'import_log_id' => $this->importLog->id,
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }

    /**
     * Handle a failed job
     */
    public function failed(Throwable $exception): void
    {
        $this->importLog->update([
            'status' => 'failed',
            'error_message' => $exception->getMessage(),
            'finished_at' => now(),
        ]);

        Log::error("Book import job failed after retries", [
            'import_log_id' => $this->importLog->id,
            'error' => $exception->getMessage(),
        ]);
    }
}
