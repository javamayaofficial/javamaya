<?php

namespace App\Services\PaymentGateways;

use App\Models\Order;
use Illuminate\Http\Request;

class DuitkuGateway extends AbstractGateway
{
    public function code(): string { return 'duitku'; }

    public function isConfigured(): bool
    {
        return filled($this->cred('merchant_code', 'DUITKU_MERCHANT_CODE'))
            && filled($this->cred('api_key', 'DUITKU_API_KEY'));
    }

    public function charge(Order $order): array
    {
        $merchantCode = $this->cred('merchant_code', 'DUITKU_MERCHANT_CODE');
        $apiKey       = $this->cred('api_key', 'DUITKU_API_KEY');
        $base = $this->sandbox()
            ? 'https://sandbox.duitku.com/webapi/api/merchant'
            : 'https://passport.duitku.com/webapi/api/merchant';

        $signature = md5($merchantCode . $order->order_ref . $order->total . $apiKey);
        $result = $this->http('createInvoice', 'post', "$base/v2/inquiry", [
            'merchantCode'    => $merchantCode,
            'paymentAmount'   => $order->total,
            'merchantOrderId' => $order->order_ref,
            'productDetails'  => $order->items->pluck('product_name')->implode(', '),
            'email'           => $order->buyer_email,
            'phoneNumber'     => $order->buyer_phone,
            'callbackUrl'     => route('webhooks.duitku'),
            'returnUrl'       => route('checkout.thankyou', $order->order_ref),
            'signature'       => $signature,
            'expiryPeriod'    => (int) config('javamaya.order.pending_expiry_minutes'),
        ]);

        if ($result['ok'] && isset($result['json']['paymentUrl'])) {
            return ['mode' => 'redirect', 'url' => $result['json']['paymentUrl']];
        }
        return ['mode' => 'error', 'message' => 'Metode Duitku sedang gangguan, silakan pilih metode lain.'];
    }

    public function verifyWebhook(Request $request): bool
    {
        // Signature callback Duitku: md5(merchantCode + amount + merchantOrderId + apiKey)
        $merchantCode = $this->cred('merchant_code', 'DUITKU_MERCHANT_CODE');
        $apiKey       = $this->cred('api_key', 'DUITKU_API_KEY');
        $expected = md5($merchantCode . $request->input('amount') . $request->input('merchantOrderId') . $apiKey);
        return hash_equals($expected, (string) $request->input('signature'));
    }

    public function parseWebhook(array $payload): array
    {
        return [
            'order_ref' => $payload['merchantOrderId'] ?? null,
            'amount'    => (int) ($payload['amount'] ?? 0),
            'paid'      => ($payload['resultCode'] ?? null) === '00',
            'reference' => $payload['reference'] ?? null,
        ];
    }
}
