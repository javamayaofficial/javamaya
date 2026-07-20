<?php

namespace App\Services\Checkout;

use App\Models\Order;
use App\Models\ProductUpsell;
use Illuminate\Support\Str;

/**
 * Upsell pasca-pembelian: setelah order LUNAS, halaman thank-you menawarkan
 * satu produk lanjutan dengan harga spesial. Menerima tawaran = order pending
 * baru (data pembeli sama, gateway sama) -> langsung ke halaman bayar.
 */
class UpsellService
{
    public function __construct(protected CheckoutService $checkout) {}

    /** Tawaran untuk order ini: upsell aktif dari produk utama pertama yang BELUM dimiliki pembeli. */
    public function offerFor(Order $order): ?ProductUpsell
    {
        if (! $order->isPaid()) return null;

        $mainIds = $order->items->where('is_bump', false)->pluck('product_id');
        $upsell = ProductUpsell::with('upsellProduct')
            ->whereIn('main_product_id', $mainIds)
            ->where('active', true)->first();
        if (! $upsell || ! $upsell->upsellProduct || $upsell->upsellProduct->status !== 'published') return null;

        // Jangan tawarkan produk yang sudah dimiliki
        if ($order->user_id && \App\Services\LMS\MemberAccessGrantor::hasActiveAccess($order->user, $upsell->upsell_product_id)) {
            return null;
        }
        return $upsell;
    }

    /** Terima tawaran -> order baru harga spesial. Idempotent per order asal. */
    public function accept(Order $sourceOrder, ProductUpsell $upsell): Order
    {
        $idem = 'upsell-' . $sourceOrder->order_ref . '-' . $upsell->id;
        $existing = Order::where('idempotency_key', $idem)->first();
        if ($existing) return $existing;

        $product = $upsell->upsellProduct;
        // Pakai CheckoutService agar pajak + kode unik + snapshot konsisten,
        // lalu timpa harga item ke harga spesial upsell.
        $order = $this->checkout->createOrder([
            'product'         => $product,
            'buyer_name'      => $sourceOrder->buyer_name,
            'buyer_email'     => $sourceOrder->buyer_email,
            'buyer_phone'     => $sourceOrder->buyer_phone,
            'gateway_code'    => $sourceOrder->gateway_code,
            'idempotency_key' => $idem,
            'user_id'         => $sourceOrder->user_id,
            'affiliate_slug'  => $sourceOrder->affiliate_slug,
        ]);

        if ($upsell->upsell_price < $product->price) {
            $diff = $product->price - $upsell->upsell_price;
            $tax = app(\App\Services\Invoice\TaxCalculator::class)->calculate($upsell->upsell_price);
            $order->items()->update(['price' => $upsell->upsell_price]);
            $order->update([
                'subtotal'      => $upsell->upsell_price,
                'discount'      => 0,
                'tax_amount'    => $tax['tax_amount'],
                'total'         => $tax['total'],
                'total_payable' => $tax['total'] + (int) $order->unique_code,
            ]);
        }
        \App\Models\FunnelEvent::create([
            'stage' => 'upsell_accepted', 'product_id' => $product->id,
            'session_hash' => hash('sha256', (string) session()->getId()), 'event_date' => today(),
        ]);
        return $order->fresh();
    }
}
