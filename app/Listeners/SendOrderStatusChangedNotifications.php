<?php

namespace App\Listeners;

use App\Events\OrderStatusChanged;
use App\Notifications\OrderStatusChangedNotification;
use App\Models\User;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class SendOrderStatusChangedNotifications implements ShouldQueue
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
    public function handle(OrderStatusChanged $event): void
    {
        // Notify customer
        $event->order->user->notify(new OrderStatusChangedNotification($event->order));
        
        // Also notify admins of status changes
        User::where('role', 'admin')->get()->each->notify(new OrderStatusChangedNotification($event->order));
    }
}
