<?php

namespace App\Http\Controllers;

use App\Models\Book;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;

class CartController extends Controller
{
    public function index(Request $request)
    {
        $cart = $request->session()->get('cart', []);
        $bookIds = array_keys($cart);
        $books = Book::whereIn('id', $bookIds)->get()->keyBy('id');

        $normalized = [];
        $adjusted = false;
        foreach ($cart as $bookId => $qty) {
            if (! isset($books[$bookId])) {
                $adjusted = true;

                continue;
            }
            $stock = (int) $books[$bookId]->stock_quantity;
            $validQty = max(0, min((int) $qty, $stock));
            if ($validQty !== (int) $qty) {
                $adjusted = true;
            }
            if ($validQty > 0) {
                $normalized[$bookId] = $validQty;
            }
        }

        if ($adjusted) {
            $request->session()->put('cart', $normalized);
            $request->session()->flash('warning', 'Some item quantities were adjusted based on current stock.');
        }

        $items = [];
        $total = 0;
        foreach ($normalized as $bookId => $qty) {
            $book = $books[$bookId];
            $max = (int) $book->stock_quantity;
            $subtotal = $qty * $book->price;
            $items[] = compact('book', 'qty', 'max', 'subtotal');
            $total += $subtotal;
        }

        return view('cart.index', compact('items', 'total'));
    }

    public function add(Request $request)
    {
        $data = $request->validate([
            'book_id' => ['required', 'exists:books,id'],
            'quantity' => ['required', 'integer', 'min:1'],
        ]);

        $book = Book::findOrFail($data['book_id']);
        $stock = (int) $book->stock_quantity;
        $cart = $request->session()->get('cart', []);
        $current = (int) ($cart[$book->id] ?? 0);
        $desired = $current + (int) $data['quantity'];
        $final = min($desired, $stock);
        $cart[$book->id] = $final;
        $request->session()->put('cart', $cart);

        if ($final < $desired) {
            return back()->with('warning', "Only {$stock} copies available for \"{$book->title}\". Quantity adjusted.");
        }

        return back()->with('success', 'Added to cart.');
    }

    public function update(Request $request)
    {
        $items = $request->validate([
            'items' => ['required', 'array'],
            'items.*.book_id' => ['required', 'exists:books,id'],
            'items.*.quantity' => ['required', 'integer', 'min:0'],
        ])['items'];

        $cart = [];
        $bookIds = Arr::pluck($items, 'book_id');
        $books = Book::whereIn('id', $bookIds)->get()->keyBy('id');
        $adjusted = false;

        foreach ($items as $item) {
            $book = $books[$item['book_id']] ?? null;
            if (! $book) {
                $adjusted = true;

                continue;
            }
            $qty = (int) $item['quantity'];
            $max = (int) $book->stock_quantity;
            $final = max(0, min($qty, $max));
            if ($final !== $qty) {
                $adjusted = true;
            }
            if ($final > 0) {
                $cart[$book->id] = $final;
            }
        }

        $request->session()->put('cart', $cart);

        return back()->with($adjusted ? 'warning' : 'success', $adjusted ? 'Some quantities were adjusted based on stock.' : 'Cart updated.');
    }

    public function remove(Request $request, Book $book)
    {
        $cart = $request->session()->get('cart', []);
        unset($cart[$book->id]);
        $request->session()->put('cart', $cart);

        return back()->with('success', 'Item removed.');
    }
}
