<?php

namespace App\Filament\Pages;

use App\Models\ActivityLog;
use App\Models\Setting;
use App\Models\WebhookIdempotency;
use Filament\Notifications\Notification;
use Filament\Pages\Page;

/** API Keys & Webhook URL Overview: semua URL callback siap copy + regenerate cron secret. */
class WebhookUrlOverview extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-link';
    protected static ?string $navigationGroup = 'Integrasi & Pembayaran';
    protected static ?string $navigationLabel = 'Webhook URL & Cron';
    protected static ?string $title = 'API Keys & Webhook URL Overview';
    protected static string $view = 'filament.pages.webhook-url-overview';

    public function getViewData(): array
    {
        $seen = fn (string $gw) => WebhookIdempotency::where('gateway', $gw)->exists();
        return [
            'rows' => [
                ['label' => 'Moota — Webhook URL', 'url' => route('webhooks.moota'),
                 'hint' => 'Tempel di Moota > Integrations > Webhook. Isi Secret yang sama dengan kredensial moota (webhook_secret).',
                 'status' => $seen('moota')],
                ['label' => 'Duitku — Callback URL', 'url' => route('webhooks.duitku'),
                 'hint' => 'Tempel di Duitku Dashboard > Proyek Anda > Callback URL.',
                 'status' => $seen('duitku')],
                ['label' => 'Xendit — Webhook URL (invoice paid)', 'url' => route('webhooks.xendit'),
                 'hint' => 'Tempel di Xendit Dashboard > Settings > Webhooks > Invoice paid. Simpan Verification Token ke kredensial xendit (webhook_token).',
                 'status' => $seen('xendit')],
                ['label' => 'Cron URL (opsional, tiap 1-5 menit)', 'url' => route('cron.run', ['secret' => jm_setting('cron_secret', env('CRON_SECRET'))]),
                 'hint' => 'Pasang di cPanel > Cron Jobs: curl -s "URL_INI". Tanpa cron, sistem tetap jalan via kunjungan pengunjung.',
                 'status' => null],
            ],
        ];
    }

    public function regenerateCronSecret(): void
    {
        $new = str()->random(40);
        Setting::put('cron_secret', $new);
        ActivityLog::record('cron_secret.regenerated');
        Notification::make()->title('Cron secret baru dibuat')
            ->body('Secret lama langsung nonaktif. Perbarui cron job Anda dengan URL baru.')->success()->send();
    }
}
