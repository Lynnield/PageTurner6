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
        return ['mail', 'database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'message' => 'Order status updated successfully! (Order #' . $this->order->id . ' is now ' . ucfirst($this->order->status) . ')',
            'order_id' => $this->order->id,
            'status' => $this->order->status,
            'type' => 'order_status_changed'
        ];
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
