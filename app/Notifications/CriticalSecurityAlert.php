<?php

namespace App\Notifications;

use App\Models\Audit;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class CriticalSecurityAlert extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public Audit $audit) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Critical Security Alert: ' . ucfirst($this->audit->event))
            ->level('error')
            ->line('A critical security event has occurred in the system.')
            ->line('Event: ' . $this->audit->event)
            ->line('Target: ' . class_basename($this->audit->auditable_type) . ' (ID: ' . $this->audit->auditable_id . ')')
            ->line('IP Address: ' . $this->audit->ip_address)
            ->action('View Audit Log', route('admin.audits.show', $this->audit->id));
    }
}
