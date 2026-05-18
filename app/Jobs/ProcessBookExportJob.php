<?php

namespace App\Jobs;

use App\Events\ExportCompleted;
use App\Exports\BookExport;
use App\Models\ExportLog;
use App\Services\AuditService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Excel as ExcelFormat;
use Maatwebsite\Excel\Facades\Excel;
use Throwable;

/**
 * ProcessBookExportJob: Queue large book export operations
 * Handles format conversion and file storage
 */
class ProcessBookExportJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 2;
    public $timeout = 3600;

    /**
     * Create a new job instance.
     */
    public function __construct(
        private int $exportLogId
    ) {
        $this->onQueue('exports');
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        ini_set('memory_limit', '1024M');
        set_time_limit(0);

        $exportLog = ExportLog::findOrFail($this->exportLogId);
        try {
            $exportLog->update(['status' => 'processing']);

            $export = new BookExport($exportLog->filters, $exportLog->columns);

            $writerType = match ($exportLog->format) {
                'csv' => ExcelFormat::CSV,
                'xlsx' => ExcelFormat::XLSX,
                'pdf' => ExcelFormat::DOMPDF,
                default => ExcelFormat::XLSX,
            };

            $exportLog->update(['status' => 'exporting']);

            // Store to disk
            Excel::store(
                $export,
                $exportLog->stored_path,
                $exportLog->file_disk,
                $writerType
            );

            // Get row count
            $rowCount = (clone $export->query())->count();

            $exportLog->update([
                'status' => 'completed',
                'total_rows' => $rowCount,
                'finished_at' => now(),
            ]);

            // Log to audit trail
            AuditService::logExport($exportLog->id, $rowCount, $exportLog->format);

            // Dispatch completion event
            ExportCompleted::dispatch($exportLog);

            Log::info("Book export completed", [
                'export_log_id' => $exportLog->id,
                'rows' => $rowCount,
                'format' => $exportLog->format,
            ]);

        } catch (Throwable $e) {
            $exportLog->update([
                'status' => 'failed',
                'error_message' => $e->getMessage(),
                'finished_at' => now(),
            ]);

            Log::error("Book export failed", [
                'export_log_id' => $exportLog->id,
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
        $exportLog = ExportLog::find($this->exportLogId);
        if ($exportLog) {
            $exportLog->update([
                'status' => 'failed',
                'error_message' => $exception->getMessage(),
                'finished_at' => now(),
            ]);
        }

        Log::error("Book export job failed after retries", [
            'export_log_id' => $this->exportLogId,
            'error' => $exception->getMessage(),
        ]);
    }
}
