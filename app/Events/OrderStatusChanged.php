<?php

namespace App\Events;

use App\Models\Order;

class OrderStatusChanged
{
    public function __construct(public Order $order, public string $oldStatus, public string $newStatus) {}
}
