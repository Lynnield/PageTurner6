<?php

namespace App\Providers;

use App\Events\OrderPlaced;
use App\Events\OrderStatusChanged;
use App\Events\ReviewSubmitted;
use App\Models\User;
use App\Notifications\NewOrderForAdmin;
use App\Notifications\OrderPlacedNotification;
use App\Notifications\OrderStatusChangedNotification;
use App\Notifications\ReviewSubmittedNotification;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void {}

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Event::listen(OrderPlaced::class, function (OrderPlaced $event) {
            $event->order->user->notify(new OrderPlacedNotification($event->order));
            User::where('role', 'admin')->get()->each->notify(new NewOrderForAdmin($event->order));
        });

        Event::listen(OrderStatusChanged::class, function (OrderStatusChanged $event) {
            $event->order->user->notify(new OrderStatusChangedNotification($event->order));
        });

        Event::listen(ReviewSubmitted::class, function (ReviewSubmitted $event) {
            User::where('role', 'admin')->get()->each->notify(new ReviewSubmittedNotification($event->review));
        });
    }
}
