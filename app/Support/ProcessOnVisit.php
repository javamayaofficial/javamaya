<?php

namespace App\Support;

use App\Models\CronHeartbeat;
use App\Models\Order;
use App\Services\LMS\AccessExpiryEnforcer;
use App\Services\OutboundWebhook\DeliveryProcessor;
use App\Services\Webhook\InboundWebhookProcessor;
use Illuminate\Support\Facades\Cache;

/**
 * "Process on visit": traffic pengunjung memicu scheduler tick maksimal ~1x/menit
 * (cache lock 55 dtk), dengan batas kerja per tick (default 20 job / 5 detik)
 * agar tidak memperlambat pengunjung. Cron opsional memanggil runTick('cron').
 * Keduanya menulis cron_heartbeats -> widget "Cron terakhir jalan X menit lalu".
 */
class ProcessOnVisit
{
    public static function maybeTick(): void
    {
        $lock = Cache::lock('jvm-scheduler-tick', (int) config('javamaya.process_on_visit.lock_seconds'));
        if (! $lock->get()) return; // tick lain baru saja jalan; lock expire sendiri
        static::runTick('visit');
    }

    public static function runTick(string $source): array
    {
        $started = microtime(true);
        $maxSeconds = (int) config('javamaya.process_on_visit.max_seconds_per_tick');
        $limit = (int) config('javamaya.process_on_visit.max_jobs_per_tick');
        $jobs = 0; $errors = 0;

        try {
            // 1) Order pending kedaluwarsa -> expired (kode unik nominal otomatis terlepas)
            $expired = Order::where('status', 'pending')->where('expires_at', '<=', now())->limit($limit)->get();
            foreach ($expired as $order) { $order->update(['status' => 'expired']); $jobs++; }

            // 2) Retry webhook payment yang gagal diproses (backoff 1m/5m/30m/2h -> dead letter)
            if ((microtime(true) - $started) < $maxSeconds) {
                $jobs += app(InboundWebhookProcessor::class)->processRetries(10);
            }

            // 3) Outbound webhook due (backoff 1m/5m/30m/2h/12h -> dead)
            if ((microtime(true) - $started) < $maxSeconds) {
                $jobs += app(DeliveryProcessor::class)->run(10);
            }

            // 4) Access expiry: tandai + kirim notif "perpanjang di sini"
            if ((microtime(true) - $started) < $maxSeconds) {
                $jobs += app(AccessExpiryEnforcer::class)->run(10);
            }

            // 5) Email sequences due (drip)
            if ((microtime(true) - $started) < $maxSeconds) {
                $jobs += app(\App\Services\Marketing\EmailSequenceProcessor::class)->run(10);
            }

            // 6) Broadcast batch (throttled per kampanye)
            if ((microtime(true) - $started) < $maxSeconds) {
                $jobs += app(\App\Services\Marketing\BroadcastSender::class)->run(2);
            }

            // 7) Abandoned cart reminder (1 jam & 6 jam, max 2x)
            if ((microtime(true) - $started) < $maxSeconds) {
                $jobs += app(\App\Services\Marketing\AbandonedCartProcessor::class)->run(10);
            }
        } catch (\Throwable $e) {
            $errors++;
            report($e);
        }

        CronHeartbeat::beat($source, $jobs, $errors);
        return ['jobs' => $jobs, 'errors' => $errors];
    }
}
