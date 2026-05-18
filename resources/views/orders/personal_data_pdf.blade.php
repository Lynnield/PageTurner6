<!DOCTYPE html>
<html>
<head>
    <title>Personal Data Export - {{ $user->name }}</title>
    <style>
        body { font-family: sans-serif; font-size: 12px; }
        .header { border-bottom: 1px solid #ccc; padding-bottom: 10px; margin-bottom: 20px; }
        .section { margin-bottom: 20px; }
        .section-title { font-weight: bold; font-size: 14px; margin-bottom: 10px; background: #f4f4f4; padding: 5px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #eee; padding: 8px; text-align: left; }
        th { background: #fafafa; }
    </style>
</head>
<body>
    <div class="header">
        <h1>Personal Data Export</h1>
        <p>User: {{ $user->name }} ({{ $user->email }})</p>
        <p>Export Date: {{ now()->toDateTimeString() }}</p>
    </div>

    <div class="section">
        <div class="section-title">Profile Information</div>
        <table>
            <tr><th>Name</th><td>{{ $user->name }}</td></tr>
            <tr><th>Email</th><td>{{ $user->email }}</td></tr>
            <tr><th>Account Created</th><td>{{ $user->created_at }}</td></tr>
        </table>
    </div>

    <div class="section">
        <div class="section-title">Order History ({{ count($data['orders']) }})</div>
        @if(count($data['orders']) > 0)
            <table>
                <thead>
                    <tr>
                        <th>Order ID</th>
                        <th>Date</th>
                        <th>Total</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($data['orders'] as $order)
                        <tr>
                            <td>#{{ $order['id'] }}</td>
                            <td>{{ $order['created_at'] }}</td>
                            <td>${{ number_format($order['total_amount'], 2) }}</td>
                            <td>{{ ucfirst($order['status']) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @else
            <p>No orders found.</p>
        @endif
    </div>

    <div class="section">
        <div class="section-title">Reviews ({{ count($data['reviews']) }})</div>
        @if(count($data['reviews']) > 0)
            <table>
                <thead>
                    <tr>
                        <th>Book</th>
                        <th>Rating</th>
                        <th>Comment</th>
                        <th>Date</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($data['reviews'] as $review)
                        <tr>
                            <td>{{ $review['book']['title'] }}</td>
                            <td>{{ $review['rating'] }} / 5</td>
                            <td>{{ $review['comment'] }}</td>
                            <td>{{ $review['created_at'] }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @else
            <p>No reviews found.</p>
        @endif
    </div>
</body>
</html>
