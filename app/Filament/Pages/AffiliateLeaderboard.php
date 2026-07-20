<?php

namespace App\Filament\Pages;

use App\Models\Commission;
use Filament\Pages\Page;

/** Leaderboard affiliate: top performer bulan ini + sepanjang masa. */
class AffiliateLeaderboard extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-trophy';
    protected static ?string $navigationGroup = 'Marketing';
    protected static ?string $navigationLabel = 'Leaderboard Affiliate';
    protected static ?string $title = 'Leaderboard Affiliate';
    protected static string $view = 'filament.pages.affiliate-leaderboard';

    public function getViewData(): array
    {
        $rank = function ($query) {
            return $query->selectRaw('affiliate_id, COUNT(DISTINCT order_id) as orders_count, SUM(amount) as total')
                ->whereIn('status', ['pending', 'approved', 'paid'])
                ->groupBy('affiliate_id')->orderByDesc('total')->limit(10)
                ->with('affiliate.user')->get();
        };
        return [
            'monthly' => $rank(Commission::where('created_at', '>=', now()->startOfMonth())),
            'alltime' => $rank(Commission::query()),
        ];
    }
}
