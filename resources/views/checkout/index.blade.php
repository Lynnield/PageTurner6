<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Checkout
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    @if(session('warning'))
                        <div class="mb-4 p-3 rounded bg-yellow-50 text-yellow-800 border border-yellow-200">{{ session('warning') }}</div>
                    @endif
                    @if(session('error'))
                        <div class="mb-4 p-3 rounded bg-red-50 text-red-700 border border-red-200">{{ session('error') }}</div>
                    @endif

                    @if(empty($items))
                        <p class="text-gray-500">Your cart is empty.</p>
                        <a href="{{ route('books.index') }}" class="text-indigo-600 hover:underline">Browse Books</a>
                    @else
                        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                            <div class="lg:col-span-2">
                                <h3 class="text-lg font-bold mb-4">Order Summary</h3>
                                <div class="overflow-x-auto">
                                    <table class="min-w-full divide-y divide-gray-200">
                                        <thead class="bg-gray-50">
                                            <tr>
                                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Book</th>
                                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Qty</th>
                                                <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Subtotal</th>
                                            </tr>
                                        </thead>
                                        <tbody class="bg-white divide-y divide-gray-200">
                                            @foreach($items as $i)
                                                <tr>
                                                    <td class="px-6 py-4">
                                                        <div class="font-semibold text-gray-900">{{ $i['book']->title_en ?? $i['book']->title }}</div>
                                                        <div class="text-sm text-gray-500">{{ $i['book']->author }}</div>
                                                        <div class="text-xs text-gray-500 mt-1">Available: {{ $i['max'] }}</div>
                                                    </td>
                                                    <td class="px-6 py-4 text-sm text-gray-900">{{ $i['final'] }}</td>
                                                    <td class="px-6 py-4 text-right text-sm text-gray-900">${{ number_format($i['subtotal'], 2) }}</td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                        <tfoot class="bg-gray-50">
                                            <tr>
                                                <td colspan="2" class="px-6 py-4 text-right font-bold text-gray-900">Total</td>
                                                <td class="px-6 py-4 text-right font-bold text-gray-900 text-lg">${{ number_format($total, 2) }}</td>
                                            </tr>
                                        </tfoot>
                                    </table>
                                </div>
                            </div>
                            <div>
                                <h3 class="text-lg font-bold mb-4">Shipping Details</h3>
                                <form action="{{ route('checkout.process') }}" method="POST" class="space-y-4">
                                    @csrf
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Recipient Name</label>
                                        <input type="text" name="shipping_name" value="{{ old('shipping_name') }}" required class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50" placeholder="Full name">
                                        @error('shipping_name')
                                            <div class="text-red-600 text-sm mt-1">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 mb-1">Province</label>
                                            <input type="text" name="shipping_province" value="{{ old('shipping_province') }}" required class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50" placeholder="e.g., Metro Manila">
                                            @error('shipping_province') <div class="text-red-600 text-sm mt-1">{{ $message }}</div> @enderror
                                        </div>
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 mb-1">City</label>
                                            <input type="text" name="shipping_city" value="{{ old('shipping_city') }}" required class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50" placeholder="e.g., Quezon City">
                                            @error('shipping_city') <div class="text-red-600 text-sm mt-1">{{ $message }}</div> @enderror
                                        </div>
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 mb-1">Barangay</label>
                                            <input type="text" name="shipping_barangay" value="{{ old('shipping_barangay') }}" required class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50" placeholder="e.g., Bagong Pag-asa">
                                            @error('shipping_barangay') <div class="text-red-600 text-sm mt-1">{{ $message }}</div> @enderror
                                        </div>
                                        <div>
                                            <label class="block text-sm font-medium text-gray-700 mb-1">Postal Code</label>
                                            <input type="text" name="shipping_postal_code" value="{{ old('shipping_postal_code') }}" required class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50" placeholder="e.g., 1105">
                                            @error('shipping_postal_code') <div class="text-red-600 text-sm mt-1">{{ $message }}</div> @enderror
                                        </div>
                                        <div class="md:col-span-2">
                                            <label class="block text-sm font-medium text-gray-700 mb-1">Street Address</label>
                                            <input type="text" name="shipping_street" value="{{ old('shipping_street') }}" required class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50" placeholder="Street name / subdivision">
                                            @error('shipping_street') <div class="text-red-600 text-sm mt-1">{{ $message }}</div> @enderror
                                        </div>
                                        <div class="md:col-span-2">
                                            <label class="block text-sm font-medium text-gray-700 mb-1">Building/House Number</label>
                                            <input type="text" name="shipping_building_number" value="{{ old('shipping_building_number') }}" required class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50" placeholder="e.g., Unit 12B, 1234">
                                            @error('shipping_building_number') <div class="text-red-600 text-sm mt-1">{{ $message }}</div> @enderror
                                        </div>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-gray-700 mb-1">Address Notes (optional)</label>
                                        <textarea name="shipping_address" rows="2" class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50" placeholder="Landmarks or delivery instructions">{{ old('shipping_address') }}</textarea>
                                        @error('shipping_address') <div class="text-red-600 text-sm mt-1">{{ $message }}</div> @enderror
                                    </div>
                                    <button type="submit" class="w-full bg-indigo-600 text-white px-4 py-2 rounded-md hover:bg-indigo-700 transition">
                                        Place Order
                                    </button>
                                </form>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
