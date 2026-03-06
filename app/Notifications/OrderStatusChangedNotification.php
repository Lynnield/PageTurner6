<?php

namespace App\Notifications;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class OrderStatusChangedNotification extends Notification
{
    use Queueable;

    public function __construct(public Order $order) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Order Status Updated')
            ->line('The status of your order has changed.')
            ->line('Order ID: '.$this->order->id)
            ->line('New Status: '.$this->order->status)
            ->action('View Order', route('orders.show', $this->order));
    }
}
