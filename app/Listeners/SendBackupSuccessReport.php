<?php

namespace App\Listeners;

use App\Events\BackupSucceeded;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;

class SendBackupSuccessReport implements ShouldQueue
{
    use InteractsWithQueue;

    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(BackupSucceeded $event): void
    {
        // Log successful backup
        Log::info('Backup completed successfully', [
            'size' => $event->size,
            'path' => $event->path,
            'timestamp' => now()->toDateTimeString(),
        ]);

        // Update backup monitoring record if available
        try {
            \App\Models\BackupMonitoring::create([
                'status' => 'completed',
                'size_bytes' => $event->size,
                'path' => $event->path,
                'message' => 'Backup completed successfully',
            ]);
        } catch (\Throwable $e) {
            Log::error('Failed to log backup monitoring', ['error' => $e->getMessage()]);
        }
    }
}
