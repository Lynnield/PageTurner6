<?php

namespace App\Http\Controllers;

use App\Models\Book;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class CheckoutController extends Controller
{
    public function show(Request $request)
    {
        $cart = $request->session()->get('cart', []);
        if (empty($cart)) {
            return redirect()->route('cart.index')->with('warning', 'Your cart is empty.');
        }

        $books = Book::whereIn('id', array_keys($cart))->get()->keyBy('id');
        $items = [];
        $total = 0;
        $adjusted = false;
        foreach ($cart as $bookId => $qty) {
            if (! isset($books[$bookId])) {
                $adjusted = true;

                continue;
            }
            $book = $books[$bookId];
            $max = (int) $book->stock_quantity;
            $final = max(0, min((int) $qty, $max));
            if ($final !== (int) $qty) {
                $adjusted = true;
            }
            if ($final > 0) {
                $subtotal = $final * $book->price;
                $items[] = compact('book', 'final', 'max', 'subtotal');
                $total += $subtotal;
            }
        }
        if ($adjusted) {
            $normalized = [];
            foreach ($items as $i) {
                $normalized[$i['book']->id] = $i['final'];
            }
            $request->session()->put('cart', $normalized);
            $request->session()->flash('warning', 'Some item quantities were adjusted based on current stock.');
        }

        return view('checkout.index', compact('items', 'total'));
    }

    public function process(Request $request)
    {
        $request->validate([
            'shipping_name' => ['required', 'string', 'min:2'],
            'shipping_province' => ['required', 'string'],
            'shipping_city' => ['required', 'string'],
            'shipping_barangay' => ['required', 'string'],
            'shipping_postal_code' => ['required', 'string'],
            'shipping_street' => ['required', 'string'],
            'shipping_building_number' => ['required', 'string'],
            'shipping_address' => ['nullable', 'string'],
        ]);

        $cart = $request->session()->get('cart', []);
        if (empty($cart)) {
            return redirect()->route('cart.index')->with('warning', 'Your cart is empty.');
        }

        try {
            $order = DB::transaction(function () use ($cart, $request) {
                $total = 0;
                $itemsToCreate = [];

                foreach ($cart as $bookId => $qty) {
                    $book = Book::lockForUpdate()->find($bookId);
                    if (! $book) {
                        throw new \Exception('One or more items are no longer available.');
                    }
                    $qty = (int) $qty;
                    if ($qty < 1) {
                        continue;
                    }
                    if ($book->stock_quantity < $qty) {
                        throw new \Exception("Not enough stock for: {$book->title}");
                    }
                    $book->decrement('stock_quantity', $qty);
                    $unit = $book->price;
                    $total += $unit * $qty;
                    $itemsToCreate[] = [
                        'book_id' => $book->id,
                        'quantity' => $qty,
                        'unit_price' => $unit,
                    ];
                }

                if (empty($itemsToCreate)) {
                    throw new \Exception('No valid items to checkout.');
                }

                $order = Order::create([
                    'user_id' => Auth::id(),
                    'total_amount' => $total,
                    'status' => 'pending',
                    'shipping_name' => $request->shipping_name,
                    'shipping_address' => $request->shipping_address,
                    'shipping_province' => $request->shipping_province,
                    'shipping_city' => $request->shipping_city,
                    'shipping_barangay' => $request->shipping_barangay,
                    'shipping_postal_code' => $request->shipping_postal_code,
                    'shipping_street' => $request->shipping_street,
                    'shipping_building_number' => $request->shipping_building_number,
                ]);

                foreach ($itemsToCreate as $payload) {
                    $order->items()->create($payload);
                }

                return $order;
            });

            $request->session()->forget('cart');

            return redirect()->route('orders.show', $order)->with('success', 'Order placed successfully!');
        } catch (\Throwable $e) {
            return back()->with('error', $e->getMessage())->withInput();
        }
    }
}
