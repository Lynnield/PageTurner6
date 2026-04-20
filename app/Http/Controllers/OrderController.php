<?php

namespace App\Http\Controllers;

use App\Events\OrderPlaced;
use App\Events\OrderStatusChanged;
use App\Models\Book;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class OrderController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $user = Auth::user();

        if ($user->isAdmin()) {
            $orders = Order::with(['user', 'items.book'])->latest()->paginate(10);

            return view('admin.orders.index', compact('orders'));
        }

        $orders = $user->orders()->with(['items.book'])->latest()->paginate(10);

        return view('orders.index', compact('orders'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'items' => 'required|array|min:1',
            'items.*.book_id' => 'required|exists:books,id',
            'items.*.quantity' => 'required|integer|min:1',
            'shipping_name' => ['required', 'string', 'min:2'],
            'shipping_province' => ['required', 'string'],
            'shipping_city' => ['required', 'string'],
            'shipping_barangay' => ['required', 'string'],
            'shipping_postal_code' => ['required', 'string'],
            'shipping_street' => ['required', 'string'],
            'shipping_building_number' => ['required', 'string'],
            'shipping_address' => ['nullable', 'string'],
        ]);

        try {
            DB::beginTransaction();

            $totalAmount = 0;
            $orderItemsData = [];

            foreach ($request->items as $item) {
                $book = Book::lockForUpdate()->find($item['book_id']);

                if ($book->stock_quantity < $item['quantity']) {
                    throw new \Exception("Not enough stock for book: {$book->title}");
                }

                $book->decrement('stock_quantity', $item['quantity']);

                $unitPrice = $book->price;
                $subtotal = $unitPrice * $item['quantity'];
                $totalAmount += $subtotal;

                $orderItemsData[] = [
                    'book_id' => $book->id,
                    'quantity' => $item['quantity'],
                    'unit_price' => $unitPrice,
                ];
            }

            $order = Order::create([
                'user_id' => Auth::id(),
                'total_amount' => $totalAmount,
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

            foreach ($orderItemsData as $data) {
                $order->items()->create($data);
            }

            DB::commit();

            event(new OrderPlaced($order));

            return redirect()->route('orders.show', $order)->with('success', 'Order placed successfully!');

        } catch (\Exception $e) {
            DB::rollBack();

            return back()->with('error', 'Order failed: '.$e->getMessage());
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Order $order)
    {
        $user = Auth::user();

        if ($order->user_id !== $user->id && ! $user->isAdmin()) {
            abort(403);
        }

        $order->load(['items.book', 'user']);

        return view('orders.show', compact('order'));
    }

    public function exportInvoice(Order $order)
    {
        $user = Auth::user();

        if ($order->user_id !== $user->id && ! $user->isAdmin()) {
            abort(403);
        }

        $order->load(['items.book', 'user']);

        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('orders.invoice', compact('order'));

        return $pdf->download("invoice_{$order->id}.pdf");
    }

    /**
     * Update the status of the order (Admin only).
     */
    public function updateStatus(Request $request, Order $order)
    {
        // Middleware should handle auth/admin check, but double check here or trust route group
        if (! Auth::user()->isAdmin()) {
            abort(403);
        }

        $validated = $request->validate([
            'status' => ['required', Rule::in(['pending', 'processing', 'completed', 'cancelled'])],
        ]);

        $old = $order->status;
        $order->update(['status' => $validated['status']]);
        event(new OrderStatusChanged($order, $old, $order->status));

        return back()->with('success', 'Order status updated successfully.');
    }
}
