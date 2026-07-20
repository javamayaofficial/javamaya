<?php

namespace App\Services\Checkout;

use App\Models\Coupon;
use App\Models\Product;

class CouponValidator
{
    /** @return array{ok: bool, coupon?: Coupon, discount?: int, message?: string} */
    public function validate(string $code, Product $product, int $subtotal, ?string $buyerEmail = null): array
    {
        $coupon = Coupon::where('code', strtoupper(trim($code)))->where('active', true)->first();
        if (! $coupon) return $this->fail();
        if ($coupon->starts_at && $coupon->starts_at->isFuture()) return $this->fail();
        if ($coupon->expires_at && $coupon->expires_at->isPast()) return $this->fail();

        if ($coupon->max_uses_total && $coupon->usages()->count() >= $coupon->max_uses_total) return $this->fail();
        if ($coupon->max_uses_per_user && $buyerEmail) {
            $usedCount = \App\Models\Order::where('coupon_id', $coupon->id)
                ->where('buyer_email', $buyerEmail)->whereIn('status', ['pending', 'paid'])->count();
            if ($usedCount >= $coupon->max_uses_per_user) return $this->fail('Kupon sudah mencapai batas pemakaian Anda.');
        }

        $included = $coupon->product_ids;
        if (is_array($included) && count($included) && ! in_array($product->id, $included)) return $this->fail('Kupon tidak berlaku untuk produk ini.');
        $excluded = $coupon->excluded_product_ids;
        if (is_array($excluded) && in_array($product->id, $excluded)) return $this->fail('Kupon tidak berlaku untuk produk ini.');

        $discount = $coupon->type === 'fixed'
            ? min((int) $coupon->value, $subtotal)
            : (int) round($subtotal * min(100, (int) $coupon->value) / 100);

        return ['ok' => true, 'coupon' => $coupon, 'discount' => $discount];
    }

    protected function fail(string $message = 'Kupon tidak berlaku.'): array
    {
        return ['ok' => false, 'message' => $message];
    }
}
