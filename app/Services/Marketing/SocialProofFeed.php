<?php

namespace App\Services\Marketing;

use App\Models\Order;

/**
 * Social proof: bubble "Bud** dari pembelian terbaru baru saja membeli X".
 * Data dari order lunas 7 hari terakhir, nama disamarkan, tanpa data sensitif.
 * Toggle via Settings social_proof_enabled.
 */
class SocialProofFeed
{
    public function recent(int $limit = 10): array
    {
        if (! filter_var(jm_setting('social_proof_enabled', '1'), FILTER_VALIDATE_BOOL)) return [];

        return Order::with('items')
            ->where('status', 'paid')->where('paid_at', '>=', now()->subDays(7))
            ->latest('paid_at')->limit($limit)->get()
            ->map(function (Order $order) {
                $item = $order->items->firstWhere('is_bump', false);
                return [
                    'name'    => $this->mask($order->buyer_name),
                    'product' => $item?->product_name,
                    'ago'     => $order->paid_at->diffForHumans(),
                ];
            })->filter(fn ($row) => $row['product'])->values()->all();
    }

    protected function mask(string $name): string
    {
        $first = trim(explode(' ', trim($name))[0] ?? '');
        if (mb_strlen($first) <= 3) return $first . '**';
        return mb_substr($first, 0, 3) . str_repeat('*', min(4, mb_strlen($first) - 3));
    }
}
