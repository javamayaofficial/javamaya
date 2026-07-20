<?php

namespace App\Services\PaymentGateways;

use App\Models\Order;
use Illuminate\Http\Request;

/** Transfer manual: instruksi rekening + kode unik; konfirmasi manual admin (atau Moota bila aktif). */
class ManualTransferGateway extends AbstractGateway
{
    public function code(): string { return 'manual_transfer'; }

    public function isConfigured(): bool
    {
        return filled(jm_setting('manual_account_number', env('MANUAL_TRANSFER_ACCOUNT_NUMBER')));
    }

    public function charge(Order $order): array
    {
        return [
            'mode' => 'instruction',
            'view' => 'checkout.instructions.transfer',
            'data' => [
                'bank_name'      => jm_setting('manual_bank_name', env('MANUAL_TRANSFER_BANK_NAME')),
                'account_number' => jm_setting('manual_account_number', env('MANUAL_TRANSFER_ACCOUNT_NUMBER')),
                'account_holder' => jm_setting('manual_account_holder', env('MANUAL_TRANSFER_ACCOUNT_HOLDER')),
                'amount'         => $order->total_payable,
                'auto_confirm'   => false,
                'note'           => 'Setelah transfer, pembayaran akan dikonfirmasi oleh admin.',
            ],
        ];
    }

    public function verifyWebhook(Request $request): bool { return false; } // tidak punya webhook
    public function parseWebhook(array $payload): array { return ['order_ref' => null, 'amount' => 0, 'paid' => false, 'reference' => null]; }
}
