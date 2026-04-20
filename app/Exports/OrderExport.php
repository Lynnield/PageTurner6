<?php

namespace App\Exports;

use App\Models\Order;
use Illuminate\Database\Eloquent\Builder;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\ShouldQueue;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class OrderExport implements FromQuery, WithHeadings, WithMapping, ShouldQueue
{
    public function __construct(
        private readonly array $filters,
        private readonly string $type = 'admin' // admin|customer|financial
    ) {}

    public function query(): Builder
    {
        $q = Order::query()->with(['user', 'items.book']);

        if (!empty($this->filters['status'])) {
            $q->where('status', $this->filters['status']);
        }

        if (!empty($this->filters['date_from'])) {
            $q->whereDate('created_at', '>=', $this->filters['date_from']);
        }

        if (!empty($this->filters['date_to'])) {
            $q->whereDate('created_at', '<=', $this->filters['date_to']);
        }

        if (!empty($this->filters['user_id'])) {
            $q->where('user_id', $this->filters['user_id']);
        }

        return $q->orderBy('id');
    }

    public function headings(): array
    {
        if ($this->type === 'financial') {
            return [
                'Order ID',
                'Date',
                'Customer',
                'Subtotal',
                'Tax (Estimated 10%)',
                'Total Revenue'
            ];
        }

        return [
            'Order ID',
            'Customer Name',
            'Customer Email',
            'Status',
            'Total Amount',
            'Items Count',
            'Created At'
        ];
    }

    public function map($order): array
    {
        /** @var \App\Models\Order $order */
        if ($this->type === 'financial') {
            $tax = $order->total_amount * 0.10;
            $subtotal = $order->total_amount - $tax;
            return [
                $order->id,
                $order->created_at->format('Y-m-d H:i:s'),
                $order->user->name ?? 'Guest',
                number_format($subtotal, 2),
                number_format($tax, 2),
                number_format($order->total_amount, 2),
            ];
        }

        return [
            $order->id,
            $order->user->name ?? 'Guest',
            $order->user->email ?? 'N/A',
            ucfirst($order->status),
            $order->total_amount,
            $order->items->sum('quantity'),
            $order->created_at->format('Y-m-d H:i:s'),
        ];
    }
}
