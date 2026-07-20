<?php

namespace App\Services\Marketing;

use App\Models\BroadcastCampaign;
use App\Models\BroadcastRecipient;
use App\Models\Order;
use App\Models\User;
use App\Services\Notifications\NotificationService;

/**
 * Broadcast WA/email: admin buat kampanye + segmen -> penerima diantrekan ->
 * tick mengirim bertahap sesuai throttle_per_minute (anti banned WA).
 */
class BroadcastSender
{
    public function __construct(protected NotificationService $notifier) {}

    /** Antre penerima berdasarkan segmen: all_customers | buyers_of:<product_id> | leads */
    public function queueRecipients(BroadcastCampaign $campaign): int
    {
        $segment = $campaign->segment['type'] ?? 'all_customers';
        $field = $campaign->channel === 'wa' ? 'phone' : 'email';

        $recipients = match (true) {
            $segment === 'all_customers' => User::where('role', 'customer')->whereNotNull($field)->pluck($field),
            str_starts_with($segment, 'buyers_of:') => Order::where('status', 'paid')
                ->whereHas('items', fn ($q) => $q->where('product_id', (int) substr($segment, 10)))
                ->pluck($campaign->channel === 'wa' ? 'buyer_phone' : 'buyer_email'),
            $segment === 'leads' => \App\Models\Lead::where('verified', true)
                ->whereNotNull($campaign->channel === 'wa' ? 'phone' : 'email')
                ->pluck($campaign->channel === 'wa' ? 'phone' : 'email'),
            default => collect(),
        };

        $count = 0;
        foreach ($recipients->filter()->unique() as $recipient) {
            BroadcastRecipient::firstOrCreate(
                ['broadcast_campaign_id' => $campaign->id, 'recipient' => $recipient],
                ['status' => 'queued']
            );
            $count++;
        }
        $campaign->update(['status' => 'sending']);
        return $count;
    }

    /** Kirim batch sesuai throttle. Dipanggil tick tiap ~menit. */
    public function run(int $maxCampaigns = 2): int
    {
        $sent = 0;
        $campaigns = BroadcastCampaign::where('status', 'sending')->limit($maxCampaigns)->get();
        foreach ($campaigns as $campaign) {
            $batch = BroadcastRecipient::where('broadcast_campaign_id', $campaign->id)
                ->where('status', 'queued')->limit(max(1, (int) $campaign->throttle_per_minute))->get();

            if ($batch->isEmpty()) { $campaign->update(['status' => 'done']); continue; }

            $adapter = $campaign->channel === 'wa' ? $this->notifier->waProvider() : $this->notifier->emailProvider();
            foreach ($batch as $recipient) {
                if (! $adapter) { $recipient->update(['status' => 'failed', 'error' => 'Provider belum dikonfigurasi']); continue; }
                $result = $adapter->send($recipient->recipient, $campaign->body, $campaign->subject);
                $recipient->update([
                    'status' => $result['ok'] ? 'sent' : 'failed',
                    'error' => $result['error'] ?? null,
                ]);
                $sent++;
            }
        }
        return $sent;
    }
}
