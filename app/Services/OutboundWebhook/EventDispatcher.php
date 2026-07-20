<?php

namespace App\Services\OutboundWebhook;

use App\Models\OutboundWebhookDelivery;
use App\Models\OutboundWebhookSubscription;

/**
 * Event bus keluar. dispatch() hanya MENULIS delivery record (cepat, tidak
 * memblokir pipeline order); pengiriman aktual oleh DeliveryProcessor
 * via process-on-visit / cron dengan retry + dead letter.
 */
class EventDispatcher
{
    public function dispatch(string $event, array $payload): void
    {
        $subs = OutboundWebhookSubscription::where('event', $event)->where('active', true)->get();
        foreach ($subs as $sub) {
            OutboundWebhookDelivery::create([
                'subscription_id' => $sub->id,
                'event'   => $event,
                'payload' => ['event' => $event, 'data' => $payload, 'sent_at' => now()->toIso8601String()],
                'status'  => 'pending',
                'next_retry_at' => now(),
            ]);
        }
    }
}
