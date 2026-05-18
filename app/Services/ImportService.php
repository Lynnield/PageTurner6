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
        return $this->queueImport($file, 'books', $actor, new BookImport(0, $mode), $mode);
    }

    /**
     * Queue an enterprise user import (CSV/XLSX).
     */
    public function queueUserImport(UploadedFile $file, User $actor): ImportLog
    {
        return $this->queueImport($file, 'users', $actor, new \App\Imports\UserImport(0));
    }

    private function queueImport(UploadedFile $file, string $type, User $actor, $importClass, string $mode = 'skip'): ImportLog
    {
        $disk = 'local';
        $storedPath = $file->storeAs(
            "imports/{$type}/".now()->format('Y/m/d'),
            uniqid("{$type}_", true).'_'.$file->getClientOriginalName(),
            $disk
        );

        $log = ImportLog::create([
            'user_id' => $actor->id,
            'import_type' => $type,
            'original_filename' => $file->getClientOriginalName(),
            'file_disk' => $disk,
            'stored_path' => $storedPath,
            'mode' => $mode,
            'status' => 'queued',
        ]);

        // Inject log ID into import class if it supports it
        if (property_exists($importClass, 'importLogId')) {
            $reflection = new \ReflectionProperty($importClass, 'importLogId');
            $reflection->setAccessible(true);
            $reflection->setValue($importClass, $log->id);
        }

        try {
            Excel::queueImport($importClass, $storedPath, $disk);
        } catch (Throwable $e) {
            Log::error("Failed to queue {$type} import", [
                'import_log_id' => $log->id,
                'error' => $e->getMessage(),
            ]);

            $log->update([
                'status' => 'failed',
                'error_message' => $e->getMessage(),
                'finished_at' => now(),
            ]);
        }

        return $log;
    }

    public function downloadImportFile(ImportLog $log): string
    {
        return Storage::disk($log->file_disk)->path($log->stored_path);
    }
}

