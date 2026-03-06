<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Admin Dashboard') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <div class="grid grid-cols-2 md:grid-cols-4 gap-6">
                <div class="bg-white p-6 rounded shadow"><div class="text-sm text-gray-500">{{ __('Users') }}</div><div class="text-3xl font-bold">{{ $metrics['users'] }}</div></div>
                <div class="bg-white p-6 rounded shadow"><div class="text-sm text-gray-500">{{ __('Books') }}</div><div class="text-3xl font-bold">{{ $metrics['books'] }}</div></div>
                <div class="bg-white p-6 rounded shadow"><div class="text-sm text-gray-500">{{ __('Categories') }}</div><div class="text-3xl font-bold">{{ $metrics['categories'] }}</div></div>
                <div class="bg-white p-6 rounded shadow"><div class="text-sm text-gray-500">{{ __('Orders') }}</div><div class="text-3xl font-bold">{{ $metrics['orders'] }}</div></div>
            </div>

            <div class="bg-white p-6 rounded shadow">
                <h3 class="text-lg font-bold mb-4">{{ __('Order Status Summary') }}</h3>
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                    @foreach(['pending','processing','completed','cancelled'] as $s)
                        <div class="p-4 border rounded"><div class="text-sm text-gray-500">{{ ucfirst($s) }}</div><div class="text-2xl font-bold">{{ $statusSummary[$s] ?? 0 }}</div></div>
                    @endforeach
                </div>
            </div>

            <div class="bg-white p-6 rounded shadow">
                <h3 class="text-lg font-bold mb-4">{{ __('Recent Orders') }}</h3>
                <div class="divide-y">
                    @forelse($recentOrders as $order)
                        <div class="py-3 flex justify-between">
                            <div>#{{ $order->id }} — {{ $order->user->name }}</div>
                            <div class="text-sm">{{ ucfirst($order->status) }}</div>
                        </div>
                    @empty
                        <div class="py-3 text-gray-500">{{ __('No recent orders') }}</div>
                    @endforelse
                </div>
            </div>

            <div class="bg-white p-6 rounded shadow">
                <h3 class="text-lg font-bold mb-4">{{ __('Recent Reviews') }}</h3>
                <div class="divide-y">
                    @forelse($recentReviews as $review)
                        <div class="py-3">
                            <div class="font-semibold">{{ $review->user->name }}</div>
                            <div class="text-sm text-gray-500">{{ __('Book ID:') }} {{ $review->book->id }}</div>
                            <div>{{ $review->rating }} ★ — {{ \Illuminate\Support\Str::limit($review->comment, 80) }}</div>
                        </div>
                    @empty
                        <div class="py-3 text-gray-500">{{ __('No recent reviews') }}</div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
