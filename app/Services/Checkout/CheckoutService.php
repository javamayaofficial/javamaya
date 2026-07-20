<?php

namespace App\Services\Checkout;

use App\Models\Coupon;
use App\Models\CouponUsage;
use App\Models\Order;
use App\Models\Product;
use App\Models\ProductBump;
use App\Services\Invoice\TaxCalculator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CheckoutService
{
    public function __construct(protected TaxCalculator $tax) {}

    /**
     * Buat order (idempotent by idempotency_key). Kode unik nominal (1-999)
     * direservasi per order pending untuk metode transfer/QRIS agar Moota
     * bisa match by amount tanpa nominal kembar.
     */
    public function createOrder(array $data): Order
    {
        return DB::transaction(function () use ($data) {
            // Idempotency: double-submit dengan key sama -> kembalikan order yang sudah ada.
            if (! empty($data['idempotency_key'])) {
                $existing = Order::where('idempotency_key', $data['idempotency_key'])->first();
                if ($existing) return $existing;
            }

            /** @var Product $product */
            $product = $data['product'];
            $subtotal = $product->price;
            $items = [['product' => $product, 'price' => $product->price, 'is_bump' => false]];

            // Order bump (opsional, satu bump aktif per produk)
            if (! empty($data['bump_id'])) {
                $bump = ProductBump::where('id', $data['bump_id'])
                    ->where('product_id', $product->id)->where('active', true)->first();
                if ($bump) {
                    $subtotal += $bump->bump_price;
                    $items[] = ['product' => $bump->bumpProduct, 'price' => $bump->bump_price, 'is_bump' => true];
                }
            }

            // Kupon
            $discount = 0; $coupon = null;
            if (! empty($data['coupon_code'])) {
                $result = app(CouponValidator::class)->validate($data['coupon_code'], $product, $subtotal, $data['buyer_email']);
                if ($result['ok']) { $discount = $result['discount']; $coupon = $result['coupon']; }
            }

            $afterDiscount = max(0, $subtotal - $discount);
            $taxResult = $this->tax->calculate($afterDiscount);

            $needsUniqueCode = in_array($data['gateway_code'], ['moota', 'manual_transfer', 'qris_manual'], true);
            $uniqueCode = $needsUniqueCode ? $this->reserveUniqueCode($taxResult['total']) : null;

            $order = Order::create([
                'order_ref'       => 'JVM-' . strtoupper(Str::random(8)),
                'idempotency_key' => $data['idempotency_key'] ?? null,
                'user_id'         => $data['user_id'] ?? null,
                'buyer_name'      => $data['buyer_name'],
                'buyer_email'     => strtolower($data['buyer_email']),
                'buyer_phone'     => jm_normalize_phone($data['buyer_phone']),
                'subtotal'        => $subtotal,
                'discount'        => $discount,
                'coupon_id'       => $coupon?->id,
                'tax_amount'      => $taxResult['tax_amount'],
                'tax_mode'        => $taxResult['mode'],
                'tax_percent'     => $taxResult['percent'],
                'total'           => $taxResult['total'],
                'unique_code'     => $uniqueCode,
                'total_payable'   => $taxResult['total'] + (int) $uniqueCode,
                'gateway_code'    => $data['gateway_code'],
                'status'          => 'pending',
                'expires_at'      => now()->addMinutes((int) config('javamaya.order.pending_expiry_minutes')),
                'affiliate_slug'  => $data['affiliate_slug'] ?? null,
            ]);

            foreach ($items as $item) {
                $order->items()->create([
                    'product_id'   => $item['product']->id,
                    'product_name' => $item['product']->name,
                    'price'        => $item['price'],
                    'is_bump'      => $item['is_bump'],
                ]);
            }

            if ($coupon) {
                CouponUsage::create(['coupon_id' => $coupon->id, 'order_id' => $order->id, 'user_id' => $data['user_id'] ?? null]);
            }

            return $order;
        });
    }

    /**
     * Reservasi kode unik 1-999: tidak boleh ada order pending lain dengan
     * total_payable sama (anti nominal kembar untuk Moota exact match).
     */
    protected function reserveUniqueCode(int $baseTotal): int
    {
        $usedCodes = Order::where('status', 'pending')
            ->whereNotNull('unique_code')
            ->whereBetween('total_payable', [$baseTotal + 1, $baseTotal + 999])
            ->pluck('unique_code')->map(fn ($c) => (int) $c)->all();

        for ($i = 0; $i < 50; $i++) {
            $code = random_int(1, 999);
            if (! in_array($code, $usedCodes, true)) return $code;
        }
        // Fallback deterministik bila 50 percobaan acak gagal (volume sangat tinggi)
        for ($code = 1; $code <= 999; $code++) {
            if (! in_array($code, $usedCodes, true)) return $code;
        }
        return 0; // 999 order pending dengan basis sama — konfirmasi manual tetap bisa
    }
}
