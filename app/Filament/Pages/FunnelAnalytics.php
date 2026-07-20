<?php

namespace App\Filament\Pages;

use App\Models\FunnelEvent;
use App\Models\Product;
use Filament\Pages\Page;

/** Funnel Analytics: page_view -> checkout_started -> order_created -> paid + upsell_accepted. */
class FunnelAnalytics extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-funnel';
    protected static ?string $navigationGroup = 'Marketing';
    protected static ?string $navigationLabel = 'Funnel Analytics';
    protected static ?string $title = 'Funnel Analytics';
    protected static string $view = 'filament.pages.funnel-analytics';

    public int $days = 30;
    public ?int $productId = null;

    public function getViewData(): array
    {
        $since = today()->subDays($this->days);
        $base = FunnelEvent::where('event_date', '>=', $since)
            ->when($this->productId, fn ($q) => $q->where('product_id', $this->productId));

        $count = fn (string $stage) => (clone $base)->where('stage', $stage)->count();
        $views = $count('page_view');
        $checkout = $count('checkout_started');
        $orders = $count('order_created');
        $paid = \App\Models\Order::where('status', 'paid')->where('paid_at', '>=', $since)
            ->when($this->productId, fn ($q) => $q->whereHas('items', fn ($qq) => $qq->where('product_id', $this->productId)))
            ->count();
        $upsells = $count('upsell_accepted');

        $pct = fn (int $part, int $whole) => $whole > 0 ? round($part / $whole * 100, 1) : 0;

        return [
            'stages' => [
                ['label' => 'Kunjungan halaman produk', 'count' => $views, 'pct' => 100],
                ['label' => 'Mulai checkout', 'count' => $checkout, 'pct' => $pct($checkout, $views)],
                ['label' => 'Order dibuat', 'count' => $orders, 'pct' => $pct($orders, $views)],
                ['label' => 'Lunas 💰', 'count' => $paid, 'pct' => $pct($paid, $views)],
            ],
            'upsells' => $upsells,
            'conversionRate' => $pct($paid, $views),
            'products' => Product::published()->pluck('name', 'id'),
        ];
    }
}
