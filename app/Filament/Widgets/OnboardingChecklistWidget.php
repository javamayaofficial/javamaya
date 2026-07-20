<?php

namespace App\Filament\Widgets;

use App\Models\Backup;
use App\Models\Order;
use App\Models\Product;
use App\Services\Notifications\NotificationService;
use App\Services\PaymentGateways\GatewayRegistry;
use Filament\Widgets\Widget;

/** Setup Checklist onboarding: dari install ke transaksi pertama < 30 menit. */
class OnboardingChecklistWidget extends Widget
{
    protected static ?int $sort = 0;
    protected static string $view = 'filament.widgets.onboarding-checklist';
    protected int|string|array $columnSpan = 'full';

    public static function canView(): bool
    {
        // Sembunyikan otomatis setelah transaksi pertama lunas
        return ! Order::where('status', 'paid')->exists();
    }

    protected function getViewData(): array
    {
        $notif = app(NotificationService::class);
        $items = [
            ['label' => 'Set Branding (nama, logo, warna)', 'done' => jm_setting('store_name') !== null,
             'url' => \App\Filament\Pages\StoreSettings::getUrl()],
            ['label' => 'Set Pajak / PPN & NPWP', 'done' => \App\Models\TaxSetting::query()->exists(),
             'url' => \App\Filament\Pages\StoreSettings::getUrl()],
            ['label' => 'Aktifkan minimal 1 Payment Gateway', 'done' => app(GatewayRegistry::class)->availableForCheckout()->isNotEmpty(),
             'url' => \App\Filament\Resources\PaymentGatewayResource::getUrl()],
            ['label' => 'Hubungkan WhatsApp (Fonnte) & Email (Mailketing)', 'done' => $notif->waProvider() !== null || $notif->emailProvider() !== null,
             'url' => \App\Filament\Pages\StoreSettings::getUrl()],
            ['label' => 'Buat produk pertama & publish', 'done' => Product::where('status', 'published')->exists(),
             'url' => \App\Filament\Resources\ProductResource::getUrl('create')],
            ['label' => 'Backup pertama', 'done' => Backup::where('status', 'success')->exists(),
             'url' => \App\Filament\Pages\BackupsPage::getUrl()],
        ];
        return ['items' => $items, 'doneCount' => collect($items)->where('done', true)->count()];
    }
}
