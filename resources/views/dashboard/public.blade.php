<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Public Dashboard') }}
            </h2>
            <span class="px-3 py-1 rounded-full text-xs font-semibold bg-yellow-100 text-yellow-800">
                {{ __('Read-only public view') }}
            </span>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <div class="bg-white p-6 rounded shadow flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-500">{{ __('Access mode') }}</p>
                    @auth
                        <p class="text-base font-semibold text-green-700">{{ __('Signed in – limited public view') }}</p>
                    @else
                        <p class="text-base font-semibold text-gray-800">{{ __('Guest – public overview only') }}</p>
                    @endauth
                </div>
                <div class="flex gap-3">
                    @auth
                        <a href="{{ route('dashboard') }}" class="px-4 py-2 rounded bg-indigo-600 text-white hover:bg-indigo-700 text-sm">
                            {{ __('Go to my dashboard') }}
                        </a>
                    @else
                        <a href="{{ route('login') }}" class="px-4 py-2 rounded bg-indigo-600 text-white hover:bg-indigo-700 text-sm">
                            {{ __('Log in for full dashboard') }}
                        </a>
                        <a href="{{ route('register') }}" class="px-4 py-2 rounded bg-gray-100 text-gray-800 hover:bg-gray-200 text-sm">
                            {{ __('Register') }}
                        </a>
                    @endauth
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div class="bg-white p-6 rounded shadow">
                    <p class="text-sm text-gray-500">{{ __('Available Books') }}</p>
                    <p class="text-3xl font-bold text-gray-900">{{ $metrics['books'] }}</p>
                </div>
                <div class="bg-white p-6 rounded shadow">
                    <p class="text-sm text-gray-500">{{ __('Categories') }}</p>
                    <p class="text-3xl font-bold text-gray-900">{{ $metrics['categories'] }}</p>
                </div>
                <div class="bg-white p-6 rounded shadow">
                    <p class="text-sm text-gray-500">{{ __('Completed Orders (all customers)') }}</p>
                    <p class="text-3xl font-bold text-gray-900">{{ $metrics['completed_orders'] }}</p>
                </div>
            </div>

            <div class="bg-white p-6 rounded shadow">
                <h3 class="text-lg font-bold mb-4">{{ __('Recently Added Books') }}</h3>
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                    @forelse($latestBooks as $book)
                        <div class="border rounded p-4 flex flex-col justify-between">
                            <div>
                                <div class="font-semibold text-gray-900 mb-1">{{ $book->title }}</div>
                                <div class="text-sm text-gray-600 mb-1">{{ $book->author }}</div>
                                <div class="text-xs text-gray-500">
                                    {{ $book->category->name_en ?? $book->category->name ?? __('Uncategorized') }}
                                </div>
                            </div>
                            <div class="mt-3 flex items-center justify-between text-sm text-gray-700">
                                <span>{{ __('₱') }}{{ number_format($book->price, 2) }}</span>
                                <a href="{{ route('books.show', $book) }}" class="text-indigo-600 hover:underline text-xs">
                                    {{ __('View details') }}
                                </a>
                            </div>
                        </div>
                    @empty
                        <p class="text-gray-500">{{ __('No books available yet.') }}</p>
                    @endforelse
                </div>
            </div>

            <div class="bg-white p-6 rounded shadow flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                <div>
                    <p class="font-semibold text-gray-900">{{ __('Want a personalized view?') }}</p>
                    <p class="text-sm text-gray-600">
                        {{ __('Sign in to see your orders, reviews, and security status.') }}
                    </p>
                </div>
                <div class="flex gap-3">
                    <a href="{{ route('books.index') }}" class="px-4 py-2 rounded bg-gray-100 text-gray-800 hover:bg-gray-200 text-sm">
                        {{ __('Browse all books') }}
                    </a>
                    @auth
                        <a href="{{ route('dashboard') }}" class="px-4 py-2 rounded bg-indigo-600 text-white hover:bg-indigo-700 text-sm">
                            {{ __('Open my dashboard') }}
                        </a>
                    @else
                        <a href="{{ route('login') }}" class="px-4 py-2 rounded bg-indigo-600 text-white hover:bg-indigo-700 text-sm">
                            {{ __('Log in') }}
                        </a>
                    @endauth
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
