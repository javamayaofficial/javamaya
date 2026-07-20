<?php

namespace App\Filament\Widgets;

use App\Models\Order;
use App\Models\Refund;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class KpiOverview extends StatsOverviewWidget
{
    protected static ?int $sort = 1;

    protected function getStats(): array
    {
        $today = (int) Order::where('status', 'paid')->whereDate('paid_at', today())->sum('total');
        $yesterday = (int) Order::where('status', 'paid')->whereDate('paid_at', today()->subDay())->sum('total');
        $month = (int) Order::where('status', 'paid')
            ->whereBetween('paid_at', [now()->startOfMonth(), now()])->sum('total');
        $refundMonth = (int) Refund::whereBetween('refunded_at', [now()->startOfMonth(), now()])->sum('amount');
        $refundCount = Refund::whereBetween('refunded_at', [now()->startOfMonth(), now()])->count();

        return [
            Stat::make('Revenue bulan ini', jm_rupiah($month))
                ->description(Order::where('status', 'paid')->whereBetween('paid_at', [now()->startOfMonth(), now()])->count() . ' transaksi lunas')
                ->color('success'),
            Stat::make('Revenue hari ini', jm_rupiah($today))
                ->description('Kemarin: ' . jm_rupiah($yesterday))
                ->color($today >= $yesterday ? 'success' : 'warning'),
            Stat::make('Refund bulan ini', jm_rupiah($refundMonth))
                ->description($refundCount . ' refund')
                ->color($refundCount > 0 ? 'danger' : 'gray'),
        ];
    }
}
