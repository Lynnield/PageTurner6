<?php

namespace App\Listeners;

use App\Events\BackupFailed;
use App\Models\User;
use App\Notifications\CriticalSecurityAlert;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class SendBackupFailedAlert implements ShouldQueue
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
    public function handle(BackupFailed $event): void
    {
        // Send security alert to all admins
        User::where('role', 'admin')->get()->each->notify(
            new CriticalSecurityAlert(
                "Database backup failed: {$event->message}",
                'backup_failed'
            )
        );
    }
}
