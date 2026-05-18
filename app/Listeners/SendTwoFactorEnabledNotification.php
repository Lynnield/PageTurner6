<?php

namespace App\Listeners;

use App\Events\TwoFactorEnabled;
use App\Notifications\TwoFactorEnabled as TwoFactorEnabledNotification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class SendTwoFactorEnabledNotification implements ShouldQueue
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
    public function handle(TwoFactorEnabled $event): void
    {
        // Notify user of 2FA enablement
        $event->user->notify(new TwoFactorEnabledNotification());
    }
}
