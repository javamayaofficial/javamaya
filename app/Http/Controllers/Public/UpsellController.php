<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\ProductUpsell;
use App\Services\Checkout\UpsellService;

class UpsellController extends Controller
{
    public function accept(string $orderRef, ProductUpsell $upsell, UpsellService $upsells)
    {
        $order = Order::where('order_ref', $orderRef)->firstOrFail();
        abort_unless($order->isPaid(), 403, 'Tawaran hanya berlaku setelah pembayaran.');
        abort_unless($upsells->offerFor($order)?->id === $upsell->id, 404);

        $newOrder = $upsells->accept($order, $upsell);
        return redirect()->route('checkout.pay', $newOrder->order_ref);
    }
}
