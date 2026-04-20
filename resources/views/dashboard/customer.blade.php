<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Welcome,') }} {{ $user->name }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <div class="grid grid-cols-2 md:grid-cols-4 gap-6">
                <div class="bg-white p-6 rounded shadow"><div class="text-sm text-gray-500">{{ __('Total Orders') }}</div><div class="text-3xl font-bold">{{ $orderCount }}</div></div>
                <div class="bg-white p-6 rounded shadow"><div class="text-sm text-gray-500">{{ __('Pending') }}</div><div class="text-3xl font-bold">{{ $statusSummary['pending'] ?? 0 }}</div></div>
                <div class="bg-white p-6 rounded shadow"><div class="text-sm text-gray-500">{{ __('Processing') }}</div><div class="text-3xl font-bold">{{ $statusSummary['processing'] ?? 0 }}</div></div>
                <div class="bg-white p-6 rounded shadow"><div class="text-sm text-gray-500">{{ __('Completed') }}</div><div class="text-3xl font-bold">{{ $statusSummary['completed'] ?? 0 }}</div></div>
            </div>

            <div class="bg-white p-6 rounded shadow">
                <h3 class="text-lg font-bold mb-4">{{ __('Account Security') }}</h3>
                <div class="flex items-center gap-6">
                    <div>{{ __('Email Verification:') }} <span class="{{ $emailVerified ? 'text-green-600' : 'text-red-600' }}">{{ $emailVerified ? __('Verified') : __('Unverified') }}</span></div>
                    <div>{{ __('Two-Factor Authentication:') }} <span class="{{ $twoFactorEnabled ? 'text-green-600' : 'text-red-600' }}">{{ $twoFactorEnabled ? __('Enabled') : __('Disabled') }}</span></div>
                    <a href="{{ route('two-factor.settings') }}" class="text-indigo-600 hover:underline">{{ __('Manage Security') }}</a>
                </div>
            </div>

            <div class="bg-white p-6 rounded shadow">
                <h3 class="text-lg font-bold mb-4">{{ __('Recent Orders') }}</h3>
                <div class="divide-y">
                    @forelse($orders as $order)
                        <div class="py-3 flex justify-between">
                            <div>#{{ $order->id }}</div>
                            <div class="text-sm">{{ ucfirst($order->status) }}</div>
                        </div>
                    @empty
                        <div class="py-3 text-gray-500">{{ __('No recent orders') }}</div>
                    @endforelse
                </div>
            </div>

            <div class="bg-white p-6 rounded shadow">
                <h3 class="text-lg font-bold mb-4">{{ __('Your Reviews') }}</h3>
                <div class="divide-y">
                    @forelse($reviews as $review)
                        <div class="py-3">
                            <div class="text-sm text-gray-500">{{ __('Book ID:') }} {{ $review->book->id }}</div>
                            <div>{{ $review->rating }} ★ — {{ \Illuminate\Support\Str::limit($review->comment, 80) }}</div>
                        </div>
                    @empty
                        <div class="py-3 text-gray-500">{{ __('No reviews yet') }}</div>
                    @endforelse
                </div>
            </div>

            <div class="flex gap-4 flex-wrap">
                <a href="{{ route('books.index') }}" class="px-4 py-2 bg-indigo-600 text-white rounded">{{ __('Browse Books') }}</a>
                <a href="{{ route('orders.index') }}" class="px-4 py-2 bg-gray-200 rounded">{{ __('Order History') }}</a>
                <a href="{{ route('profile.edit') }}" class="px-4 py-2 bg-gray-200 rounded">{{ __('Profile Settings') }}</a>
                <a href="{{ route('two-factor.settings') }}" class="px-4 py-2 bg-gray-200 rounded">{{ __('Security Settings') }}</a>
            </div>

            <!-- Data Portability Section -->
            <div class="bg-white p-6 rounded shadow mt-8 border-t-4 border-indigo-500">
                <h3 class="text-lg font-bold mb-2 flex items-center gap-2">
                    <svg class="w-5 h-5 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
                    {{ __('Data Portability & GDPR') }}
                </h3>
                <p class="text-sm text-gray-600 mb-4">{{ __('Download your personal data and account history in structured formats.') }}</p>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <a href="{{ route('customer.export.data') }}" class="flex flex-col items-center justify-center p-4 border rounded hover:bg-gray-50 transition">
                        <span class="font-semibold text-gray-800">{{ __('Export My Data') }}</span>
                        <span class="text-xs text-gray-500 mt-1">{{ __('Full profile, reviews, and activity (JSON)') }}</span>
                    </a>
                    <a href="{{ route('customer.export.orders') }}" class="flex flex-col items-center justify-center p-4 border rounded hover:bg-gray-50 transition">
                        <span class="font-semibold text-gray-800">{{ __('Export Order History') }}</span>
                        <span class="text-xs text-gray-500 mt-1">{{ __('Detailed list of all purchases (Excel)') }}</span>
                    </a>
                    <a href="{{ route('customer.export.history') }}" class="flex flex-col items-center justify-center p-4 border rounded hover:bg-gray-50 transition">
                        <span class="font-semibold text-gray-800">{{ __('Export Reading History') }}</span>
                        <span class="text-xs text-gray-500 mt-1">{{ __('Books purchased and reviewed (CSV)') }}</span>
                    </a>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
