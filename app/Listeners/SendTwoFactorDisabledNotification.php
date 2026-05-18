<?php

namespace App\Listeners;

use App\Events\TwoFactorDisabled;
use App\Notifications\TwoFactorDisabled as TwoFactorDisabledNotification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class SendTwoFactorDisabledNotification implements ShouldQueue
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
    public function handle(TwoFactorDisabled $event): void
    {
        // Notify user of 2FA disablement
        $event->user->notify(new TwoFactorDisabledNotification());
    }
}
