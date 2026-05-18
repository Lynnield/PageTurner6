<?php

namespace App\Providers;

use App\Events\BackupFailed;
use App\Events\BackupSucceeded;
use App\Events\ExportCompleted;
use App\Events\ImportCompleted;
use App\Events\OrderPlaced;
use App\Events\OrderStatusChanged;
use App\Events\ReviewSubmitted;
use App\Events\TwoFactorDisabled;
use App\Events\TwoFactorEnabled;
use App\Listeners\SendOrderPlacedNotifications;
use App\Listeners\SendOrderStatusChangedNotifications;
use App\Listeners\SendReviewSubmittedNotifications;
use App\Listeners\SendBackupFailedAlert;
use App\Listeners\SendBackupSuccessReport;
use App\Listeners\SendImportCompletedNotification;
use App\Listeners\SendExportCompletedNotification;
use App\Listeners\SendTwoFactorEnabledNotification;
use App\Listeners\SendTwoFactorDisabledNotification;
use Illuminate\Auth\Events\Failed;
use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Logout;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event to listener mappings for the application.
     *
     * @var array<class-string, array<int, class-string>>
     */
    protected $listen = [
        OrderPlaced::class => [
            SendOrderPlacedNotifications::class,
        ],
        OrderStatusChanged::class => [
            SendOrderStatusChangedNotifications::class,
        ],
        ReviewSubmitted::class => [
            SendReviewSubmittedNotifications::class,
        ],
        BackupFailed::class => [
            SendBackupFailedAlert::class,
        ],
        BackupSucceeded::class => [
            SendBackupSuccessReport::class,
        ],
        ImportCompleted::class => [
            SendImportCompletedNotification::class,
        ],
        ExportCompleted::class => [
            SendExportCompletedNotification::class,
        ],
        TwoFactorEnabled::class => [
            SendTwoFactorEnabledNotification::class,
        ],
        TwoFactorDisabled::class => [
            SendTwoFactorDisabledNotification::class,
        ],
        Login::class => [
            \App\Listeners\LogAuthAction::class,
        ],
        Logout::class => [
            \App\Listeners\LogAuthAction::class,
        ],
        Failed::class => [
            \App\Listeners\LogAuthAction::class,
        ],
        PasswordReset::class => [
            \App\Listeners\LogAuthAction::class,
        ],
    ];

    /**
     * Register any events for your application.
     */
    public function boot(): void
    {
        //
    }
}
