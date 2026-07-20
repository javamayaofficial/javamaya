<?php

namespace App\Services\Affiliate;

use App\Models\Affiliate;
use App\Models\Commission;
use App\Models\CommissionRule;
use App\Models\Order;
use App\Models\Referral;

class CommissionCalculator
{
    /** Dipanggil dari OrderPaidPipeline (dalam transaksi). Self-purchase tidak berkomisi. */
    public function recordForOrder(Order $order): void
    {
        if (! $order->affiliate_slug) return;
        $affiliate = Affiliate::where('slug', $order->affiliate_slug)->where('status', 'active')->first();
        if (! $affiliate) return;
        if ((int) $affiliate->user_id === (int) $order->user_id) return; // self-purchase

        $amount = 0;
        foreach ($order->items as $item) {
            $rule = CommissionRule::where('product_id', $item->product_id)->first()
                ?? CommissionRule::whereNull('product_id')->first();
            if (! $rule) continue;
            $base = $item->price; // dari harga setelah diskon proporsional? MVP: harga item snapshot
            $amount += $rule->type === 'fixed'
                ? (int) $rule->value
                : (int) round($base * min(90, (int) $rule->value) / 100);
        }
        if ($amount <= 0) return;

        Referral::firstOrCreate(['order_id' => $order->id], ['affiliate_id' => $affiliate->id]);
        Commission::create([
            'affiliate_id' => $affiliate->id, 'order_id' => $order->id,
            'amount' => $amount, 'status' => 'pending',
        ]);
    }
}
