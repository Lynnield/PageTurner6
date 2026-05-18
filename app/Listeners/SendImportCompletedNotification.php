<?php

namespace App\Listeners;

use App\Events\ImportCompleted;
use App\Models\User;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Notification;

class SendImportCompletedNotification implements ShouldQueue
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
    public function handle(ImportCompleted $event): void
    {
        // Notify admin who initiated the import
        $admin = $event->importLog->user;
        
        if ($admin) {
            $message = $event->importLog->status === 'completed'
                ? "Import completed: {$event->importLog->total_rows} rows processed"
                : "Import failed: {$event->importLog->error_message}";

            // Store as database notification for dashboard access
            Notification::send($admin, new \App\Notifications\ImportExportNotification(
                $message,
                $event->importLog,
                'import'
            ));
        }
    }
}
