<?php

namespace App\Services\Webhook;

use App\Models\Order;
use App\Models\PaymentTransactionLog;
use App\Models\WebhookDeadLetter;
use App\Models\WebhookIdempotency;
use App\Models\WebhookRetryQueue;
use App\Services\Checkout\OrderPaidPipeline;
use App\Services\PaymentGateways\GatewayRegistry;
use Illuminate\Database\UniqueConstraintViolationException;

/**
 * SATU pintu untuk semua webhook payment inbound.
 * Urutan: signature verify -> idempotency (unique constraint DB) -> proses
 * -> gagal: retry queue (backoff 1m/5m/30m/2h) -> dead letter + tampil di admin.
 */
class InboundWebhookProcessor
{
    public function __construct(
        protected GatewayRegistry $gateways,
        protected OrderPaidPipeline $pipeline,
    ) {}

    /** @return array{status: int, body: array} respons cepat untuk gateway */
    public function handle(string $gatewayCode, \Illuminate\Http\Request $request): array
    {
        $gateway = $this->gateways->driver($gatewayCode);
        if (! $gateway) return ['status' => 404, 'body' => ['message' => 'Unknown gateway']];

        if (! $gateway->verifyWebhook($request)) {
            PaymentTransactionLog::create([
                'gateway_code' => $gatewayCode, 'direction' => 'webhook',
                'payload' => ['note' => 'signature_invalid'], 'result' => 'rejected',
            ]);
            return ['status' => 403, 'body' => ['message' => 'Invalid signature']];
        }

        $payload = $request->all() ?: (json_decode($request->getContent(), true) ?? []);
        $hash = hash('sha256', $gatewayCode . '|' . json_encode($payload));

        // Idempotency di level DB (unique constraint), bukan sekadar cek aplikasi.
        try {
            WebhookIdempotency::create(['gateway' => $gatewayCode, 'event_hash' => $hash]);
        } catch (UniqueConstraintViolationException) {
            return ['status' => 200, 'body' => ['message' => 'Already processed']];
        }

        try {
            $this->process($gatewayCode, $payload);
            WebhookIdempotency::where('gateway', $gatewayCode)->where('event_hash', $hash)
                ->update(['processed_at' => now()]);
            return ['status' => 200, 'body' => ['message' => 'OK']];
        } catch (\Throwable $e) {
            // Gagal proses -> retry queue; balas 200 agar gateway tidak spam ulang
            WebhookRetryQueue::create([
                'gateway' => $gatewayCode, 'payload' => $payload, 'event_hash' => $hash,
                'attempts' => 0, 'last_error' => $e->getMessage(),
                'next_retry_at' => now()->addMinutes(config('javamaya.webhook.retry_schedule_minutes')[0]),
            ]);
            report($e);
            return ['status' => 200, 'body' => ['message' => 'Queued for retry']];
        }
    }

    /** Dipakai handle() dan retry job. WAJIB idempotent (pipeline sudah cek isPaid). */
    public function process(string $gatewayCode, array $payload): void
    {
        $gateway = $this->gateways->driver($gatewayCode);
        $parsed  = $gateway->parseWebhook($payload);

        PaymentTransactionLog::create([
            'gateway_code' => $gatewayCode, 'direction' => 'webhook',
            'payload' => $parsed + ['raw_keys' => array_keys($payload)], 'result' => 'received',
        ]);

        if (! $parsed['paid']) return;

        $order = $parsed['order_ref']
            ? Order::where('order_ref', $parsed['order_ref'])->first()
            : $this->matchByAmount($parsed['amount']);   // Moota: exact amount match

        if (! $order) {
            // Mutasi/notifikasi tak dikenal: dicatat untuk pencocokan manual, bukan error.
            PaymentTransactionLog::create([
                'gateway_code' => $gatewayCode, 'direction' => 'webhook',
                'payload' => $parsed, 'result' => 'unmatched',
            ]);
            return;
        }

        if ($order->status === 'expired') {
            PaymentTransactionLog::create([
                'order_id' => $order->id, 'gateway_code' => $gatewayCode,
                'direction' => 'webhook', 'payload' => $parsed, 'result' => 'paid_after_expiry_flag',
            ]);
            // Kebijakan MVP: tetap lunas-kan (uang sudah masuk), admin melihat flag di log.
        }

        $this->pipeline->markPaid($order, $parsed['reference'], $payload);
    }

    protected function matchByAmount(int $amount): ?Order
    {
        if ($amount <= 0) return null;
        return Order::where('status', 'pending')
            ->whereIn('gateway_code', ['moota', 'manual_transfer', 'qris_manual'])
            ->where('total_payable', $amount)
            ->orderBy('created_at')->first();
    }

    /** Retry processor — dipanggil dari process-on-visit / cron. */
    public function processRetries(int $limit = 10): int
    {
        $schedule = config('javamaya.webhook.retry_schedule_minutes');
        $due = WebhookRetryQueue::where('next_retry_at', '<=', now())->limit($limit)->get();

        foreach ($due as $item) {
            try {
                $this->process($item->gateway, $item->payload);
                $item->delete();
            } catch (\Throwable $e) {
                $attempts = $item->attempts + 1;
                if ($attempts >= count($schedule)) {
                    WebhookDeadLetter::create([
                        'gateway' => $item->gateway, 'payload' => $item->payload,
                        'last_error' => $e->getMessage(), 'attempts' => $attempts,
                    ]);
                    $item->delete();
                    // Alert admin via notif in-app (widget dashboard membaca dead letters)
                } else {
                    $item->update([
                        'attempts' => $attempts, 'last_error' => $e->getMessage(),
                        'next_retry_at' => now()->addMinutes($schedule[$attempts]),
                    ]);
                }
            }
        }
        return $due->count();
    }
}
