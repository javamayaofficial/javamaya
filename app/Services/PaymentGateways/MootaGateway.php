<?php

namespace App\Services\PaymentGateways;

use App\Models\Order;
use Illuminate\Http\Request;

/**
 * Moota: deteksi mutasi bank. Order transfer diberi kode unik nominal (1-999),
 * webhook mutasi dicocokkan by nominal exact (total_payable). Tanpa upload bukti.
 */
class MootaGateway extends AbstractGateway
{
    public function code(): string { return 'moota'; }

    public function isConfigured(): bool
    {
        return filled($this->cred('webhook_secret', 'MOOTA_WEBHOOK_SECRET'));
        // MOOTA_TOKEN opsional (untuk cek saldo/mutasi via API di admin); webhook cukup secret.
    }

    public function charge(Order $order): array
    {
        // Moota bukan payment page — instruksi transfer + nominal unik.
        return [
            'mode' => 'instruction',
            'view' => 'checkout.instructions.transfer',
            'data' => [
                'bank_name'      => jm_setting('manual_bank_name', env('MANUAL_TRANSFER_BANK_NAME')),
                'account_number' => jm_setting('manual_account_number', env('MANUAL_TRANSFER_ACCOUNT_NUMBER')),
                'account_holder' => jm_setting('manual_account_holder', env('MANUAL_TRANSFER_ACCOUNT_HOLDER')),
                'amount'         => $order->total_payable,
                'auto_confirm'   => true,
                'note'           => 'Transfer TEPAT sampai 3 digit terakhir agar terkonfirmasi otomatis.',
            ],
        ];
    }

    public function verifyWebhook(Request $request): bool
    {
        // Moota mengirim header Signature = HMAC-SHA256(rawBody, secret)
        $secret = (string) $this->cred('webhook_secret', 'MOOTA_WEBHOOK_SECRET');
        $signature = (string) $request->header('Signature', $request->header('signature', ''));
        if ($secret === '' || $signature === '') return false;
        $expected = hash_hmac('sha256', $request->getContent(), $secret);
        return hash_equals($expected, $signature);
    }

    public function parseWebhook(array $payload): array
    {
        // Payload Moota: array mutasi; ambil kredit (type CR) -> match by amount.
        $mutation = $payload[0] ?? $payload;
        $isCredit = strtoupper((string) ($mutation['type'] ?? '')) === 'CR';
        return [
            'order_ref' => null,                                   // matching by amount
            'amount'    => (int) ($mutation['amount'] ?? 0),
            'paid'      => $isCredit,
            'reference' => (string) ($mutation['mutation_id'] ?? ($mutation['id'] ?? '')),
        ];
    }
}
