<?php

namespace App\Services\LMS;

use App\Models\MemberAccess;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class MemberAccessGrantor
{
    /**
     * Grant akses untuk semua item order. Bundle di-expand ke tiap item
     * mengikuti access_expiry masing-masing item. User dibuat otomatis
     * dari data checkout bila belum ada (password acak -> reset via email/OTP).
     */
    public function grantForOrder(Order $order): void
    {
        $user = $this->resolveUser($order);
        $order->update(['user_id' => $user->id]);

        foreach ($order->items as $item) {
            $product = $item->product;
            if (! $product) continue;

            if ($product->type === 'bundle' && $product->bundle) {
                foreach ($product->bundle->items as $bundleItem) {
                    $this->grant($user, $bundleItem->itemProduct, $order, 'bundle_item');
                }
            } else {
                $this->grant($user, $product, $order, 'product');
            }
        }
    }

    protected function grant(User $user, Product $product, Order $order, string $sourceType): void
    {
        MemberAccess::create([
            'user_id'     => $user->id,
            'source_type' => $sourceType,
            'source_id'   => $product->id,
            'product_id'  => $product->id,
            'order_id'    => $order->id,
            'starts_at'   => now(),
            'expires_at'  => $product->accessExpiresAt(),
        ]);
    }

    protected function resolveUser(Order $order): User
    {
        return User::firstOrCreate(
            ['email' => $order->buyer_email],
            [
                'name'     => $order->buyer_name,
                'phone'    => $order->buyer_phone,
                'password' => Hash::make(Str::random(32)),
                'role'     => 'customer',
            ]
        );
    }

    /** Punya akses aktif ke produk? Dipakai middleware konten & download. */
    public static function hasActiveAccess(User $user, int $productId): bool
    {
        return MemberAccess::where('user_id', $user->id)
            ->where('product_id', $productId)
            ->whereNull('revoked_at')
            ->where(fn ($q) => $q->whereNull('expires_at')->orWhere('expires_at', '>', now()))
            ->exists();
    }
}
