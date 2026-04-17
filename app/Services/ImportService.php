<?php

namespace App\Services;

use App\Imports\BookImport;
use App\Models\ImportLog;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;
use Throwable;

class ImportService
{
    /**
     * Queue an enterprise book import (CSV/XLSX).
     */
    public function queueBookImport(UploadedFile $file, string $mode, User $actor): ImportLog
    {
        $disk = 'local';
        $storedPath = $file->storeAs(
            'imports/books/'.now()->format('Y/m/d'),
            uniqid('books_', true).'_'.$file->getClientOriginalName(),
            $disk
        );

        $log = ImportLog::create([
            'user_id' => $actor->id,
            'import_type' => 'books',
            'original_filename' => $file->getClientOriginalName(),
            'file_disk' => $disk,
            'stored_path' => $storedPath,
            'mode' => $mode,
            'status' => 'queued',
        ]);

        try {
            Excel::queueImport(
                new BookImport($log->id, $mode),
                $storedPath,
                $disk
            );
        } catch (Throwable $e) {
            Log::error('Failed to queue book import', [
                'import_log_id' => $log->id,
                'error' => $e->getMessage(),
            ]);

            $log->update([
                'status' => 'failed',
                'error_message' => $e->getMessage(),
                'finished_at' => now(),
            ]);

            // If queueing fails, keep the file for later debugging.
        }

        return $log;
    }

    public function downloadImportFile(ImportLog $log): string
    {
        return Storage::disk($log->file_disk)->path($log->stored_path);
    }
}

