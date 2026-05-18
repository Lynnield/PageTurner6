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

    public function __construct(public mixed $data, public ?string $type = null) {}

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toArray(object $notifiable): array
    {
        if ($this->data instanceof Audit) {
            return [
                'message' => 'Critical security alert: ' . $this->data->event,
                'event' => $this->data->event,
                'audit_id' => $this->data->id,
                'type' => 'security_alert'
            ];
        }

        return [
            'message' => is_string($this->data) ? $this->data : 'Critical security alert',
            'type' => $this->type ?? 'security_alert'
        ];
    }

    public function toMail(object $notifiable): MailMessage
    {
        if ($this->data instanceof Audit) {
            return (new MailMessage)
                ->subject('Critical Security Alert: ' . ucfirst($this->data->event))
                ->level('error')
                ->line('A critical security event has occurred in the system.')
                ->line('Event: ' . $this->data->event)
                ->line('Target: ' . class_basename($this->data->auditable_type) . ' (ID: ' . $this->data->auditable_id . ')')
                ->line('IP Address: ' . $this->data->ip_address)
                ->action('View Audit Log', route('admin.audits.show', $this->data->id));
        }

        return (new MailMessage)
            ->subject('Critical System Alert')
            ->level('error')
            ->line($this->data);
    }
}
