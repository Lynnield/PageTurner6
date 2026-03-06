<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ $book->title_en ?? $book->title }}
            </h2>
            <a href="{{ route('books.index') }}" class="text-sm text-indigo-600 hover:underline">Back to Books</a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200 flex flex-col md:flex-row gap-8">
                    <!-- Cover Image -->
                    <div class="w-full md:w-1/3 flex-shrink-0">
                        @if($book->cover_image)
                            <img src="{{ Storage::url($book->cover_image) }}" alt="{{ $book->title }}" class="w-full rounded shadow-lg">
                        @else
                            <div class="w-full aspect-[2/3] bg-gray-200 flex items-center justify-center rounded shadow-lg text-gray-400">
                                <svg class="w-24 h-24" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path></svg>
                            </div>
                        @endif
                    </div>

                    <!-- Details -->
                    <div class="flex-1">
                        <div class="text-sm text-indigo-600 font-bold uppercase tracking-wide mb-2">
                            <a href="{{ route('categories.show', $book->category) }}">{{ $book->category->name_en ?? $book->category->name }}</a>
                        </div>
                        <h1 class="text-3xl font-bold text-gray-900 mb-2">{{ $book->title_en ?? $book->title }}</h1>
                        <p class="text-xl text-gray-600 mb-4">by {{ $book->author }}</p>
                        
                        <div class="flex items-center mb-6">
                            <span class="text-2xl font-bold text-gray-900 mr-4">${{ number_format($book->price, 2) }}</span>
                            <div class="flex items-center text-yellow-400">
                                @for($i=1; $i<=5; $i++)
                                    <svg class="w-5 h-5 {{ $i <= round($book->average_rating) ? 'fill-current' : 'text-gray-300 fill-current' }}" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path></svg>
                                @endfor
                            </div>
                            <span class="ml-2 text-gray-600">({{ $book->reviews->count() }} reviews)</span>
                        </div>

                        <div class="prose max-w-none text-gray-700 mb-8">
                            {{ $book->description_en ?? $book->description }}
                        </div>

                        <div class="mb-4 text-sm text-gray-500">
                            ISBN: {{ $book->isbn }}
                        </div>

                        <!-- Buy Now Modal Trigger -->
                        @auth
                            <div x-data="{ openBuy: false }" class="mb-4">
                                <div class="flex items-end gap-4">
                                    <div class="w-24">
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Quantity</label>
                                        <select x-model.number="qty" class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50" x-init="qty = 1" @if($book->stock_quantity === 0) disabled @endif>
                                            @for($q=1; $q<=max(1, $book->stock_quantity); $q++)
                                                <option value="{{ $q }}">{{ $q }}</option>
                                            @endfor
                                        </select>
                                    </div>
                                    <button type="button" @click="openBuy = true" class="bg-indigo-600 text-white px-6 py-2 rounded-md hover:bg-indigo-700 transition font-bold shadow-md h-10" {{ $book->stock_quantity < 1 ? 'disabled' : '' }}>
                                        {{ $book->stock_quantity < 1 ? 'Out of Stock' : 'Buy Now' }}
                                    </button>
                                </div>
                                <p class="text-sm {{ $book->stock_quantity < 5 ? 'text-red-600' : 'text-green-600' }} mt-2 font-semibold">
                                    {{ $book->stock_quantity }} in stock
                                </p>

                                <!-- Modal -->
                                <div x-show="openBuy" x-cloak class="fixed inset-0 z-50 flex items-center justify-center">
                                    <div class="absolute inset-0 bg-black/40" @click="openBuy = false"></div>
                                    <div class="relative bg-white rounded-xl shadow-2xl w-full max-w-2xl">
                                        <div class="flex items-center justify-between p-4 border-b">
                                            <h3 class="text-lg font-bold">Shipping Details</h3>
                                            <button type="button" class="text-gray-500 hover:text-gray-700" @click="openBuy = false">✕</button>
                                        </div>
                                        <form action="{{ route('orders.store') }}" method="POST" class="p-6 space-y-4">
                                            @csrf
                                            <input type="hidden" name="items[0][book_id]" value="{{ $book->id }}">
                                            <input type="hidden" name="items[0][quantity]" :value="qty">
                                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                                <div class="md:col-span-2">
                                                    <label class="block text-sm font-medium text-gray-700 mb-1">Recipient Name</label>
                                                    <input type="text" name="shipping_name" required class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50" placeholder="Full name" value="{{ old('shipping_name') }}">
                                                </div>
                                                <div>
                                                    <label class="block text-sm font-medium text-gray-700 mb-1">Province</label>
                                                    <input type="text" name="shipping_province" value="{{ old('shipping_province') }}" required class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300">
                                                </div>
                                                <div>
                                                    <label class="block text-sm font-medium text-gray-700 mb-1">City</label>
                                                    <input type="text" name="shipping_city" value="{{ old('shipping_city') }}" required class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300">
                                                </div>
                                                <div>
                                                    <label class="block text-sm font-medium text-gray-700 mb-1">Barangay</label>
                                                    <input type="text" name="shipping_barangay" value="{{ old('shipping_barangay') }}" required class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300">
                                                </div>
                                                <div>
                                                    <label class="block text-sm font-medium text-gray-700 mb-1">Postal Code</label>
                                                    <input type="text" name="shipping_postal_code" value="{{ old('shipping_postal_code') }}" required class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300">
                                                </div>
                                                <div class="md:col-span-2">
                                                    <label class="block text-sm font-medium text-gray-700 mb-1">Street Address</label>
                                                    <input type="text" name="shipping_street" value="{{ old('shipping_street') }}" required class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300" placeholder="Street name / subdivision">
                                                </div>
                                                <div class="md:col-span-2">
                                                    <label class="block text-sm font-medium text-gray-700 mb-1">Building/House Number</label>
                                                    <input type="text" name="shipping_building_number" value="{{ old('shipping_building_number') }}" required class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300" placeholder="e.g., Unit 12B, 1234">
                                                </div>
                                                <div class="md:col-span-2">
                                                    <label class="block text-sm font-medium text-gray-700 mb-1">Address Notes (optional)</label>
                                                    <textarea name="shipping_address" rows="2" class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300" placeholder="Landmarks or delivery instructions">{{ old('shipping_address') }}</textarea>
                                                </div>
                                            </div>
                                            <div class="flex justify-end gap-3 mt-2 border-t pt-4">
                                                <button type="button" @click="openBuy = false" class="px-4 py-2 rounded bg-gray-100 hover:bg-gray-200 text-gray-800">Cancel</button>
                                                <button type="submit" class="px-4 py-2 rounded bg-indigo-600 hover:bg-indigo-700 text-white">Place Order</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                            <form action="{{ route('cart.add') }}" method="POST" class="p-4 bg-gray-50 rounded-lg border border-gray-100">
                                @csrf
                                <input type="hidden" name="book_id" value="{{ $book->id }}">
                                <div class="flex items-end gap-4">
                                    <div class="w-24">
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Quantity</label>
                                        <select name="quantity" class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50" @if($book->stock_quantity === 0) disabled @endif>
                                            @for($q=1; $q<=max(1, min($book->stock_quantity, 20)); $q++)
                                                <option value="{{ $q }}">{{ $q }}</option>
                                            @endfor
                                        </select>
                                    </div>
                                    <button type="submit" class="bg-gray-800 text-white px-6 py-2 rounded-md hover:bg-gray-900 transition font-bold shadow-md h-10" {{ $book->stock_quantity < 1 ? 'disabled' : '' }}>
                                        Add to Cart
                                    </button>
                                </div>
                            </form>
                        @else
                            <div class="bg-indigo-50 p-4 rounded mb-8 border border-indigo-100">
                                <a href="{{ route('login') }}" class="text-indigo-600 font-bold hover:underline">Log in</a> to purchase this book.
                            </div>
                        @endauth
                    </div>
                </div>

                <!-- Reviews Section -->
                <div class="p-6 bg-gray-50 border-t border-gray-200">
                    <h3 class="text-2xl font-bold mb-6">Reviews</h3>
                    
                    @auth
                        @php
                            // Check if user has purchased this book
                            $hasPurchased = Auth::user()->orders()
                                ->whereHas('items', function ($query) use ($book) {
                                    $query->where('book_id', $book->id);
                                })
                                ->where('status', 'completed')
                                ->exists();
                        @endphp

                        @if($hasPurchased)
                            <!-- Review Form -->
                            <div class="bg-white p-6 rounded-lg shadow mb-8">
                                <h4 class="text-lg font-bold mb-4">Write a Review</h4>
                                <form action="{{ route('reviews.store', $book) }}" method="POST">
                                    @csrf
                                    <div class="mb-4">
                                        <label class="block text-gray-700 text-sm font-bold mb-2">Rating</label>
                                        <div class="flex items-center gap-4">
                                            @for($i=1; $i<=5; $i++)
                                                <label class="inline-flex items-center cursor-pointer">
                                                    <input type="radio" name="rating" value="{{ $i }}" class="form-radio text-indigo-600 w-4 h-4" required>
                                                    <span class="ml-2">{{ $i }}</span>
                                                </label>
                                            @endfor
                                        </div>
                                    </div>
                                    <div class="mb-4">
                                        <label for="comment" class="block text-gray-700 text-sm font-bold mb-2">Comment</label>
                                        <textarea name="comment" id="comment" rows="3" class="shadow-sm focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm border-gray-300 rounded-md" required></textarea>
                                    </div>
                                    <button type="submit" class="bg-gray-800 text-white px-4 py-2 rounded hover:bg-gray-700 transition">Submit Review</button>
                                </form>
                            </div>
                        @else
                            <div class="mb-8 p-4 bg-yellow-50 text-yellow-800 rounded border border-yellow-200">
                                You must purchase this book to leave a review.
                            </div>
                        @endif
                    @endauth

                    <div class="space-y-6">
                        @forelse($book->reviews->sortByDesc('created_at') as $review)
                            <div class="bg-white p-4 rounded shadow border border-gray-100">
                                <div class="flex justify-between items-start">
                                    <div>
                                        <div class="flex items-center mb-1">
                                            <span class="font-bold text-gray-900 mr-2">{{ $review->user->name }}</span>
                                            <div class="flex text-yellow-400 text-sm">
                                                @for($i=1; $i<=5; $i++)
                                                    <span class="{{ $i <= $review->rating ? '' : 'text-gray-300' }}">★</span>
                                                @endfor
                                            </div>
                                        </div>
                                        <div class="text-gray-500 text-xs mb-2">{{ $review->created_at->diffForHumans() }}</div>
                                    </div>
                                    @if(Auth::check() && (Auth::id() === $review->user_id || Auth::user()->isAdmin()))
                                        <form action="{{ route('reviews.destroy', $review) }}" method="POST" onsubmit="return confirm('Are you sure?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-red-500 hover:text-red-700 text-sm">Delete</button>
                                        </form>
                                    @endif
                                </div>
                                <p class="text-gray-700">{{ $review->comment }}</p>
                            </div>
                        @empty
                            <p class="text-gray-500 italic">No reviews yet.</p>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
