<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Cart
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    @if(session('success'))
                        <div class="mb-4 p-3 rounded bg-green-50 text-green-700 border border-green-200">{{ session('success') }}</div>
                    @endif
                    @if(session('warning'))
                        <div class="mb-4 p-3 rounded bg-yellow-50 text-yellow-800 border border-yellow-200">{{ session('warning') }}</div>
                    @endif
                    @if(session('error'))
                        <div class="mb-4 p-3 rounded bg-red-50 text-red-700 border border-red-200">{{ session('error') }}</div>
                    @endif

                    @if(empty($items))
                        <p class="text-gray-500 text-center py-8">Your cart is empty.</p>
                        <div class="text-center">
                            <a href="{{ route('books.index') }}" class="text-indigo-600 hover:underline">Browse Books</a>
                        </div>
                    @else
                        <form action="{{ route('cart.update') }}" method="POST">
                            @csrf
                            @method('PATCH')
                            <div class="overflow-x-auto">
                                <table class="min-w-full divide-y divide-gray-200">
                                    <thead class="bg-gray-50">
                                        <tr>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Book</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Price</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Quantity</th>
                                            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Subtotal</th>
                                            <th class="px-6 py-3"></th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white divide-y divide-gray-200">
                                        @foreach($items as $index => $i)
                                            <tr>
                                                <td class="px-6 py-4">
                                                    <div class="font-semibold text-gray-900">{{ $i['book']->title_en ?? $i['book']->title }}</div>
                                                    <div class="text-sm text-gray-500">{{ $i['book']->author }}</div>
                                                    <div class="text-xs text-gray-500 mt-1">Only {{ $i['max'] }} in stock</div>
                                                </td>
                                                <td class="px-6 py-4 text-sm text-gray-900">${{ number_format($i['book']->price, 2) }}</td>
                                                <td class="px-6 py-4">
                                                    <input type="hidden" name="items[{{ $index }}][book_id]" value="{{ $i['book']->id }}">
                                                    <select name="items[{{ $index }}][quantity]" class="rounded border-gray-300" @if($i['max'] === 0) disabled @endif>
                                                        {{-- allow any quantity up to available stock; no arbitrary 20 limit --}}
                                                        @for($q=0;$q<=max(1, $i['max']);$q++)
                                                            <option value="{{ $q }}" {{ $q == $i['qty'] ? 'selected' : '' }}>
                                                                {{ $q === 0 ? 'Remove' : $q }}
                                                            </option>
                                                        @endfor
                                                    </select>
                                                </td>
                                                <td class="px-6 py-4 text-right text-sm text-gray-900">${{ number_format($i['subtotal'], 2) }}</td>
                                                <td class="px-6 py-4 text-right">
                                                    <button type="button" class="text-red-600 hover:text-red-800 text-sm"
                                                        onclick="const row=this.closest('tr'); const sel=row.querySelector('select[name^=items]'); if(sel){ sel.value=0; this.closest('form').submit(); }">
                                                        Remove
                                                    </button>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                    <tfoot class="bg-gray-50">
                                        <tr>
                                            <td colspan="3" class="px-6 py-4 text-right font-bold text-gray-900">Total</td>
                                            <td class="px-6 py-4 text-right font-bold text-gray-900 text-lg">${{ number_format($total, 2) }}</td>
                                            <td></td>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                            <div class="mt-6 flex justify-between">
                                <a href="{{ route('books.index') }}" class="text-indigo-600 hover:underline">&larr; Continue Shopping</a>
                                <div class="flex gap-3">
                                    <button type="submit" class="px-4 py-2 rounded bg-gray-100 hover:bg-gray-200 text-gray-800">Update Cart</button>
                                    <a href="{{ route('checkout.show') }}" class="px-4 py-2 rounded bg-indigo-600 hover:bg-indigo-700 text-white">Proceed to Checkout</a>
                                </div>
                            </div>
                        </form>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
