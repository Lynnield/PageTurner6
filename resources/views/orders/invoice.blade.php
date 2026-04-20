<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Invoice #{{ $order->id }}</title>
    <style>
        body { font-family: sans-serif; font-size: 14px; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { padding: 10px; border: 1px solid #ddd; text-align: left; }
        th { background-color: #f4f4f4; }
        .text-right { text-align: right; }
        .header { margin-bottom: 30px; }
        .header h1 { margin: 0; }
    </style>
</head>
<body>
    <div class="header">
        <h1>Invoice</h1>
        <p>Order ID: #{{ $order->id }}</p>
        <p>Date: {{ $order->created_at->format('M d, Y') }}</p>
        <p>Status: {{ ucfirst($order->status) }}</p>
    </div>

    <div>
        <strong>Billed To:</strong><br>
        {{ $order->shipping_name ?? $order->user->name }}<br>
        {{ $order->shipping_address }}<br>
        {{ $order->user->email }}
    </div>

    <table>
        <thead>
            <tr>
                <th>Item</th>
                <th>Quantity</th>
                <th>Unit Price</th>
                <th>Total</th>
            </tr>
        </thead>
        <tbody>
            @foreach($order->items as $item)
            <tr>
                <td>{{ $item->book->title ?? 'Deleted Book' }}</td>
                <td>{{ $item->quantity }}</td>
                <td>${{ number_format($item->unit_price, 2) }}</td>
                <td>${{ number_format($item->quantity * $item->unit_price, 2) }}</td>
            </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr>
                <th colspan="3" class="text-right">Total Amount</th>
                <th>${{ number_format($order->total_amount, 2) }}</th>
            </tr>
        </tfoot>
    </table>
</body>
</html>