<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class TwoFactorEnabled extends Notification
{
    use Queueable;

    public function __construct(public string $method) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Two-Factor Authentication Enabled')
            ->line('Two-factor authentication has been enabled on your account.')
            ->line('Method: '.$this->method);
    }
}
