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
        return ['mail', 'database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'message' => 'Two-factor authentication enabled successfully! (Method: ' . $this->method . ')',
            'method' => $this->method,
            'type' => '2fa_enabled'
        ];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Two-Factor Authentication Enabled')
            ->line('Two-factor authentication has been enabled on your account.')
            ->line('Method: '.$this->method);
    }
}
