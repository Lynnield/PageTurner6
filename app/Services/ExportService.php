<?php

namespace App\Services;

use App\Exports\BookExport;
use App\Models\ExportLog;
use App\Models\User;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Excel as ExcelFormat;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Throwable;

class ExportService
{
    /**
     * @param  array<string,mixed>  $filters
     * @param  list<string>  $columns
     */
    public function exportBooks(array $filters, array $columns, string $format, User $actor): array
    {
        $disk = 'local';
        $export = new BookExport($filters, $columns);

        $format = strtolower($format);
        $writerType = match ($format) {
            'csv' => ExcelFormat::CSV,
            'xlsx' => ExcelFormat::XLSX,
            'pdf' => ExcelFormat::DOMPDF,
            default => throw new \InvalidArgumentException('Unsupported export format.'),
        };

        $totalRows = (clone $export->query())->count();

        $log = ExportLog::create([
            'user_id' => $actor->id,
            'export_type' => 'books',
            'format' => $format,
            'filters' => $filters,
            'columns' => $columns,
            'status' => $totalRows > 10000 ? 'queued' : 'processing',
            'total_rows' => $totalRows,
            'file_disk' => $disk,
            'started_at' => now(),
        ]);

        $filename = sprintf(
            'exports/books/%s/books_%s.%s',
            now()->format('Y/m/d'),
            now()->format('Ymd_His').'_'.$log->id,
            $format === 'xlsx' ? 'xlsx' : ($format === 'csv' ? 'csv' : 'pdf')
        );

        if ($totalRows > 10000) {
            // Heavy exports are always queued (store to disk), user retrieves later.
            try {
                Excel::queue($export, $filename, $disk, $writerType);
                $log->update(['stored_path' => $filename]);
            } catch (Throwable $e) {
                Log::error('Failed to queue book export', ['export_log_id' => $log->id, 'error' => $e->getMessage()]);
                $log->update(['status' => 'failed', 'error_message' => $e->getMessage(), 'finished_at' => now()]);
            }

            return ['mode' => 'queued', 'log' => $log];
        }

        // Small export: stream immediately.
        try {
            /** @var StreamedResponse $response */
            $response = Excel::download($export, basename($filename), $writerType);
            // Mark as completed even though it's a streamed response (client download).
            $log->update(['status' => 'completed', 'finished_at' => now()]);

            return ['mode' => 'download', 'response' => $response, 'log' => $log];
        } catch (Throwable $e) {
            Log::error('Book export failed', ['export_log_id' => $log->id, 'error' => $e->getMessage()]);
            $log->update(['status' => 'failed', 'error_message' => $e->getMessage(), 'finished_at' => now()]);

            throw $e;
        }
    }

    public function downloadExport(ExportLog $log): string
    {
        if (! $log->stored_path) {
            throw new \RuntimeException('Export file not available.');
        }

        return Storage::disk($log->file_disk)->path($log->stored_path);
    }

    /**
     * Normalize a `fields` list to allowed columns.
     *
     * @param  list<string>  $requested
     * @return list<string>
     */
    public function normalizeBookColumns(array $requested): array
    {
        $allowed = ['id', 'isbn', 'title', 'author', 'price', 'stock', 'category', 'description', 'created_at'];
        $requested = array_values(array_filter(array_map('trim', $requested)));

        $cols = array_values(array_intersect($requested, $allowed));

        return $cols !== [] ? $cols : $allowed;
    }

    /**
     * @param array<string,mixed> $filters
     */
    public function normalizeBookFilters(array $filters): array
    {
        return [
            'category' => Arr::get($filters, 'category'),
            'price_min' => Arr::get($filters, 'price_min'),
            'price_max' => Arr::get($filters, 'price_max'),
            'stock_status' => Arr::get($filters, 'stock_status'),
            'date_from' => Arr::get($filters, 'date_from'),
            'date_to' => Arr::get($filters, 'date_to'),
        ];
    }
}

