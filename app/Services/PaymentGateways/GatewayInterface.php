<?php

namespace App\Services\PaymentGateways;

use App\Models\Order;
use Illuminate\Http\Request;

interface GatewayInterface
{
    /** Kode unik gateway, cocok dengan payment_gateways.code */
    public function code(): string;

    /** Kredensial lengkap? Gateway hanya tampil di checkout bila active + isConfigured. */
    public function isConfigured(): bool;

    /**
     * Buat tagihan. Return:
     *  ['mode' => 'redirect', 'url' => ...]  ATAU
     *  ['mode' => 'instruction', 'view' => 'checkout.instructions.xxx', 'data' => [...]]
     */
    public function charge(Order $order): array;

    /** Verifikasi signature webhook inbound. WAJIB sebelum payload diproses. */
    public function verifyWebhook(Request $request): bool;

    /**
     * Parse payload webhook -> ['order_ref' => ?string, 'amount' => ?int, 'paid' => bool, 'reference' => ?string]
     * Moota: order_ref null, matching by amount (kode unik nominal).
     */
    public function parseWebhook(array $payload): array;
}
