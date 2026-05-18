<?php

namespace App\Listeners;

use App\Events\ExportCompleted;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Notification;

class SendExportCompletedNotification implements ShouldQueue
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
    public function handle(ExportCompleted $event): void
    {
        // Notify user/admin who initiated the export
        $user = $event->exportLog->user;
        
        if ($user) {
            $message = $event->exportLog->status === 'completed'
                ? "Export completed: {$event->exportLog->total_rows} rows exported"
                : "Export failed: {$event->exportLog->error_message}";

            // Store as database notification
            Notification::send($user, new \App\Notifications\ImportExportNotification(
                $message,
                $event->exportLog,
                'export'
            ));
        }
    }
}
