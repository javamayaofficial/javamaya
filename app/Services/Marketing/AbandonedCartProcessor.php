<?php

namespace App\Services\Marketing;

use App\Models\AbandonedCartLog;
use App\Models\Order;
use App\Services\Notifications\NotificationService;

/**
 * Abandoned cart recovery: order pending tanpa bayar > 1 jam -> reminder WA+email
 * berisi link bayar. Maksimal 2 reminder (1 jam & 6 jam), berhenti saat lunas/expired.
 */
class AbandonedCartProcessor
{
    public function __construct(protected NotificationService $notifier) {}

    public function run(int $limit = 10): int
    {
        $processed = 0;

        // Reminder #1: pending > 1 jam, belum pernah diingatkan
        $firstBatch = Order::where('status', 'pending')
            ->where('created_at', '<=', now()->subHour())
            ->whereDoesntHave('abandonedLog')
            ->limit($limit)->get();
        foreach ($firstBatch as $order) {
            $this->remind($order);
            AbandonedCartLog::create(['order_id' => $order->id, 'reminders_sent' => 1, 'last_reminder_at' => now()]);
            $processed++;
        }

        // Reminder #2: 6 jam sejak dibuat, baru 1x diingatkan
        $secondBatch = AbandonedCartLog::with('order')
            ->where('reminders_sent', 1)
            ->whereHas('order', fn ($q) => $q->where('status', 'pending')->where('created_at', '<=', now()->subHours(6)))
            ->limit($limit)->get();
        foreach ($secondBatch as $log) {
            $this->remind($log->order);
            $log->update(['reminders_sent' => 2, 'last_reminder_at' => now()]);
            $processed++;
        }
        return $processed;
    }

    protected function remind(Order $order): void
    {
        $this->notifier->sendTemplate('abandoned_cart', [
            'name'      => $order->buyer_name,
            'order_ref' => $order->order_ref,
            'total'     => jm_rupiah($order->total_payable),
            'pay_url'   => route('checkout.pay', $order->order_ref),
        ], $order->buyer_phone, $order->buyer_email);
    }
}
