<?php

namespace App\Notifications;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class NewOrderForAdmin extends Notification
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
            'message' => 'A new order #' . $this->order->id . ' has been placed.',
            'order_id' => $this->order->id,
            'customer' => $this->order->user->name,
            'amount' => $this->order->total_amount,
            'type' => 'new_order_admin'
        ];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('New Order Created')
            ->line('A new order has been placed.')
            ->line('Order ID: '.$this->order->id);
    }
}
