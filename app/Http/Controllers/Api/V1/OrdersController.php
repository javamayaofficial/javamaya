<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;

class OrdersController extends Controller
{
    public function index(Request $request)
    {
        $orders = Order::with('items')
            ->when($request->query('status'), fn ($q, $s) => $q->where('status', $s))
            ->when($request->query('email'), fn ($q, $e) => $q->where('buyer_email', strtolower($e)))
            ->latest()->paginate(min(100, (int) $request->query('per_page', 20)));

        return response()->json([
            'data' => $orders->map(fn ($o) => [
                'order_ref' => $o->order_ref, 'status' => $o->status,
                'buyer' => ['name' => $o->buyer_name, 'email' => $o->buyer_email],
                'total' => $o->total_payable, 'paid_at' => $o->paid_at?->toIso8601String(),
                'items' => $o->items->map(fn ($i) => ['product' => $i->product_name, 'price' => $i->price]),
            ]),
            'meta' => ['page' => $orders->currentPage(), 'total' => $orders->total()],
        ]);
    }

    public function show(string $orderRef)
    {
        $order = Order::with('items')->where('order_ref', $orderRef)->first();
        if (! $order) {
            return response()->json(['errors' => [['code' => 'not_found', 'message' => 'Order tidak ditemukan.']]], 404);
        }
        return response()->json(['data' => [
            'order_ref' => $order->order_ref, 'status' => $order->status,
            'buyer' => ['name' => $order->buyer_name, 'email' => $order->buyer_email, 'phone' => $order->buyer_phone],
            'subtotal' => $order->subtotal, 'discount' => $order->discount,
            'tax' => ['amount' => $order->tax_amount, 'mode' => $order->tax_mode, 'percent' => (float) $order->tax_percent],
            'total' => $order->total_payable, 'paid_at' => $order->paid_at?->toIso8601String(),
        ]]);
    }
}
