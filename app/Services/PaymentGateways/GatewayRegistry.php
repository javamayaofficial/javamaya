<?php

namespace App\Services\PaymentGateways;

use App\Models\PaymentGateway;
use Illuminate\Support\Collection;

/**
 * Registry adapter. Menambah gateway baru = tambah class adapter + 1 baris di map + row DB (seeder).
 * TIDAK ADA nama provider yang di-hardcode di kode bisnis lain — semua lewat registry ini.
 */
class GatewayRegistry
{
    /** @var array<string, class-string<GatewayInterface>> */
    protected array $map = [
        'duitku'          => DuitkuGateway::class,
        'xendit'          => XenditGateway::class,
        'moota'           => MootaGateway::class,
        'manual_transfer' => ManualTransferGateway::class,
        'qris_manual'     => QRISManualGateway::class,
    ];

    public function driver(string $code): ?GatewayInterface
    {
        $config = PaymentGateway::query()->where('code', $code)->first();
        if (! $config || ! isset($this->map[$code])) return null;
        return new $this->map[$code]($config);
    }

    /** Gateway yang tampil di checkout: toggle ON + kredensial lengkap. */
    public function availableForCheckout(): Collection
    {
        return PaymentGateway::active()->get()
            ->map(fn ($cfg) => isset($this->map[$cfg->code]) ? new $this->map[$cfg->code]($cfg) : null)
            ->filter(fn ($gw) => $gw?->isConfigured())
            ->values();
    }
}
