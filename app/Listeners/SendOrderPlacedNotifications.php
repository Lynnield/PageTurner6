<?php

namespace App\Listeners;

use App\Events\OrderPlaced;
use App\Notifications\NewOrderForAdmin;
use App\Notifications\OrderPlacedNotification;
use App\Models\User;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class SendOrderPlacedNotifications implements ShouldQueue
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
    public function handle(OrderPlaced $event): void
    {
        // Notify customer
        $event->order->user->notify(new OrderPlacedNotification($event->order));

        // Notify all admins
        User::where('role', 'admin')->get()->each->notify(new NewOrderForAdmin($event->order));
    }
}
