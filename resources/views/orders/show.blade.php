<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Order #{{ $order->id }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6 bg-white border-b border-gray-200">
                    <div class="flex justify-between items-start mb-6">
                        <div>
                            <p class="text-sm text-gray-500">Placed on {{ $order->created_at->format('F d, Y \a\t h:i A') }}</p>
                            <p class="text-sm text-gray-500">Customer: {{ $order->user->name }} ({{ $order->user->email }})</p>
                        </div>
                        <div>
                            <span class="px-3 py-1 text-sm font-bold rounded-full 
                                @if($order->status === 'completed') bg-green-100 text-green-800 
                                @elseif($order->status === 'cancelled') bg-red-100 text-red-800 
                                @elseif($order->status === 'processing') bg-blue-100 text-blue-800 
                                @else bg-yellow-100 text-yellow-800 @endif">
                                {{ ucfirst($order->status) }}
                            </span>
                        </div>
                    </div>

                    @if(Auth::user()->isAdmin())
                        <div class="mb-6 p-4 bg-gray-50 rounded border border-gray-200">
                            <h3 class="text-md font-bold mb-2">Update Status</h3>
                            <form action="{{ route('admin.orders.update-status', $order) }}" method="POST" class="flex gap-2">
                                @csrf
                                @method('PATCH')
                                <select name="status" class="rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                                    <option value="pending" {{ $order->status === 'pending' ? 'selected' : '' }}>Pending</option>
                                    <option value="processing" {{ $order->status === 'processing' ? 'selected' : '' }}>Processing</option>
                                    <option value="completed" {{ $order->status === 'completed' ? 'selected' : '' }}>Completed</option>
                                    <option value="cancelled" {{ $order->status === 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                                </select>
                                <button type="submit" class="bg-indigo-600 text-white px-4 py-2 rounded hover:bg-indigo-700 transition">Update</button>
                            </form>
                        </div>
                    @endif

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                        <div class="p-4 bg-gray-50 rounded border border-gray-200">
                            <h3 class="text-md font-bold mb-2">Shipping Details</h3>
                            <div class="text-sm text-gray-700">
                                <div><span class="font-semibold">Recipient:</span> {{ $order->shipping_name ?? '—' }}</div>
                                @php
                                    $parts = array_filter([
                                        $order->shipping_building_number,
                                        $order->shipping_street,
                                        $order->shipping_barangay,
                                        $order->shipping_city,
                                        $order->shipping_province,
                                        $order->shipping_postal_code,
                                    ], fn($v) => filled($v));
                                    $addr = implode(', ', $parts);
                                @endphp
                                <div class="mt-1"><span class="font-semibold">Address:</span> {{ $addr ?: ($order->shipping_address ?? '—') }}</div>
                            </div>
                        </div>
                        <div></div>
                    </div>

                    <h3 class="text-lg font-bold mb-4">Items</h3>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200 mb-6">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Book</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Price</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Quantity</th>
                                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Subtotal</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @foreach($order->items as $item)
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm font-medium text-gray-900">{{ $item->book->title }}</div>
                                            <div class="text-sm text-gray-500">{{ $item->book->author }}</div>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">${{ number_format($item->unit_price, 2) }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $item->quantity }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 text-right">${{ number_format($item->subtotal, 2) }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                            <tfoot class="bg-gray-50">
                                <tr>
                                    <td colspan="3" class="px-6 py-4 text-right font-bold text-gray-900">Total Amount</td>
                                    <td class="px-6 py-4 text-right font-bold text-gray-900 text-lg">${{ number_format($order->total_amount, 2) }}</td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                    
                    <div class="flex justify-between">
                         <a href="{{ route('orders.index') }}" class="text-indigo-600 hover:underline">
                            &larr; Back to Orders
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
