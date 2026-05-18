<?php

namespace App\Listeners;

use App\Events\ReviewSubmitted;
use App\Notifications\ReviewSubmittedNotification;
use App\Models\User;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class SendReviewSubmittedNotifications implements ShouldQueue
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
    public function handle(ReviewSubmitted $event): void
    {
        // Notify all admins of new review
        User::where('role', 'admin')->get()->each->notify(new ReviewSubmittedNotification($event->review));
    }
}
