<?php

namespace App\Services\OutboundWebhook;

use App\Models\OutboundWebhookDelivery;
use Illuminate\Support\Facades\Http;

/** Kirim delivery due: timeout 10s, retry 1m/5m/30m/2h/12h, dead setelah 5 gagal, log per attempt. */
class DeliveryProcessor
{
    public function __construct(protected HmacSigner $signer) {}

    public function run(int $limit = 10): int
    {
        $schedule = config('javamaya.outbound_webhook.retry_schedule_minutes');
        $due = OutboundWebhookDelivery::with('subscription')
            ->whereIn('status', ['pending', 'retrying'])
            ->where('next_retry_at', '<=', now())->limit($limit)->get();

        foreach ($due as $delivery) {
            $sub = $delivery->subscription;
            if (! $sub || ! $sub->active) { $delivery->update(['status' => 'dead', 'last_error' => 'Subscription inactive']); continue; }

            $raw = json_encode($delivery->payload);
            try {
                $response = Http::timeout(10)
                    ->withHeaders([
                        'Content-Type' => 'application/json',
                        'X-Javamaya-Event' => $delivery->event,
                        'X-Javamaya-Signature' => $this->signer->sign($raw, $sub->secret),
                    ])->withBody($raw, 'application/json')->post($sub->url);

                if ($response->successful()) {
                    $delivery->update([
                        'status' => 'success', 'attempt_count' => $delivery->attempt_count + 1,
                        'response_code' => $response->status(),
                    ]);
                    continue;
                }
                $this->scheduleRetry($delivery, $schedule, 'HTTP ' . $response->status(), $response->status());
            } catch (\Throwable $e) {
                $this->scheduleRetry($delivery, $schedule, $e->getMessage(), null);
            }
        }
        return $due->count();
    }

    protected function scheduleRetry(OutboundWebhookDelivery $delivery, array $schedule, string $error, ?int $code): void
    {
        $attempts = $delivery->attempt_count + 1;
        if ($attempts >= count($schedule)) {
            $delivery->update(['status' => 'dead', 'attempt_count' => $attempts, 'last_error' => $error, 'response_code' => $code]);
            return; // tampil sebagai alert di widget admin
        }
        $delivery->update([
            'status' => 'retrying', 'attempt_count' => $attempts,
            'last_error' => $error, 'response_code' => $code,
            'next_retry_at' => now()->addMinutes($schedule[$attempts]),
        ]);
    }
}
