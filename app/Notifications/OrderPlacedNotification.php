<?php

namespace App\Notifications;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class OrderPlacedNotification extends Notification
{
    use Queueable;

    public function __construct(public Order $order) {}

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'message' => 'Order placed successfully! (Order #' . $this->order->id . ')',
            'order_id' => $this->order->id,
            'amount' => $this->order->total_amount,
            'type' => 'order_placed'
        ];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Order Placed')
            ->line('Your order has been placed.')
            ->line('Order ID: '.$this->order->id)
            ->action('View Order', route('orders.show', $this->order));
    }
}
