<?php

namespace App\Services\PaymentGateways;

use App\Models\Order;
use Illuminate\Http\Request;

/** QRIS manual: tampilkan gambar QRIS statis + nominal; konfirmasi manual admin (atau Moota bila rekening sama). */
class QRISManualGateway extends AbstractGateway
{
    public function code(): string { return 'qris_manual'; }

    public function isConfigured(): bool
    {
        return filled(jm_setting('qris_image_url', env('QRIS_IMAGE_URL')));
    }

    public function charge(Order $order): array
    {
        return [
            'mode' => 'instruction',
            'view' => 'checkout.instructions.qris',
            'data' => [
                'qris_image_url' => jm_setting('qris_image_url', env('QRIS_IMAGE_URL')),
                'amount'         => $order->total_payable,
                'note'           => 'Scan QRIS lalu bayar sesuai nominal TEPAT (termasuk 3 digit unik).',
            ],
        ];
    }

    public function verifyWebhook(Request $request): bool { return false; }
    public function parseWebhook(array $payload): array { return ['order_ref' => null, 'amount' => 0, 'paid' => false, 'reference' => null]; }
}
