<?php

namespace App\Services\PaymentGateways;

use App\Models\Order;
use Illuminate\Http\Request;

class XenditGateway extends AbstractGateway
{
    public function code(): string { return 'xendit'; }

    public function isConfigured(): bool
    {
        return filled($this->cred('api_key', 'XENDIT_API_KEY'))
            && filled($this->cred('webhook_token', 'XENDIT_WEBHOOK_TOKEN'));
    }

    public function charge(Order $order): array
    {
        $result = $this->httpWithBasicAuth('createInvoice', 'https://api.xendit.co/v2/invoices', [
            'external_id'      => $order->order_ref,
            'amount'           => $order->total,
            'payer_email'      => $order->buyer_email,
            'description'      => $order->items->pluck('product_name')->implode(', '),
            'success_redirect_url' => route('checkout.thankyou', $order->order_ref),
            'invoice_duration' => (int) config('javamaya.order.pending_expiry_minutes') * 60,
        ]);

        if ($result['ok'] && isset($result['json']['invoice_url'])) {
            return ['mode' => 'redirect', 'url' => $result['json']['invoice_url']];
        }
        return ['mode' => 'error', 'message' => 'Metode Xendit sedang gangguan, silakan pilih metode lain.'];
    }

    protected function httpWithBasicAuth(string $action, string $url, array $body): array
    {
        try {
            $response = \Illuminate\Support\Facades\Http::timeout(15)->retry(2, 500, throw: false)
                ->withBasicAuth((string) $this->cred('api_key', 'XENDIT_API_KEY'), '')
                ->post($url, $body);
            $ok = $response->successful();
            \App\Models\IntegrationLog::create([
                'provider' => 'xendit', 'action' => $action,
                'request' => ['url' => $url, 'external_id' => $body['external_id'] ?? null],
                'response' => ['status' => $response->status(), 'body' => mb_substr($response->body(), 0, 2000)],
                'success' => $ok,
            ]);
            return ['ok' => $ok, 'json' => $ok ? $response->json() : null];
        } catch (\Throwable $e) {
            \App\Models\IntegrationLog::create([
                'provider' => 'xendit', 'action' => $action,
                'request' => ['url' => $url], 'response' => ['error' => $e->getMessage()], 'success' => false,
            ]);
            return ['ok' => false, 'json' => null];
        }
    }

    public function verifyWebhook(Request $request): bool
    {
        $token = $this->cred('webhook_token', 'XENDIT_WEBHOOK_TOKEN');
        return hash_equals((string) $token, (string) $request->header('x-callback-token'));
    }

    public function parseWebhook(array $payload): array
    {
        return [
            'order_ref' => $payload['external_id'] ?? null,
            'amount'    => (int) ($payload['amount'] ?? ($payload['paid_amount'] ?? 0)),
            'paid'      => in_array($payload['status'] ?? null, ['PAID', 'SETTLED'], true),
            'reference' => $payload['id'] ?? null,
        ];
    }
}
