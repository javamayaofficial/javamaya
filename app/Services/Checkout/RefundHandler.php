<?php

namespace App\Services\Checkout;

use App\Models\ActivityLog;
use App\Models\MemberAccess;
use App\Models\Order;
use App\Models\Refund;
use App\Services\Notifications\NotificationService;
use App\Services\OutboundWebhook\EventDispatcher;
use Illuminate\Support\Facades\DB;

/**
 * Refund workflow: mark refunded + revoke SEMUA akses order + reject komisi
 * + activity log + tampil di sales report + notifikasi opsional.
 * Uang aktual dipindahkan buyer di dashboard gateway/bank (dicatat gateway_reference).
 */
class RefundHandler
{
    public function __construct(
        protected NotificationService $notifier,
        protected EventDispatcher $outbound,
    ) {}

    public function refund(Order $order, string $reason, ?int $amount, int $adminId, ?string $gatewayReference = null, bool $notifyCustomer = false): Refund
    {
        abort_unless($order->isPaid(), 422, 'Hanya order berstatus Lunas yang bisa direfund.');
        $amount = min($amount ?? $order->total_payable, $order->total_payable);

        $refund = DB::transaction(function () use ($order, $reason, $amount, $adminId, $gatewayReference) {
            $refund = Refund::create([
                'order_id' => $order->id, 'reason' => $reason, 'amount' => $amount,
                'refunded_by' => $adminId, 'gateway_reference' => $gatewayReference, 'refunded_at' => now(),
            ]);
            $order->update(['status' => 'refunded']);

            // Revoke semua akses yang lahir dari order ini (produk + item bundle + membership)
            MemberAccess::where('order_id', $order->id)->whereNull('revoked_at')
                ->update(['revoked_at' => now()]);

            // Komisi affiliate order ini -> rejected
            \App\Models\Commission::where('order_id', $order->id)
                ->whereIn('status', ['pending', 'approved'])->update(['status' => 'rejected']);

            ActivityLog::record('refund.marked', $order, ['reason' => $reason, 'amount' => $amount]);
            return $refund;
        });

        if ($notifyCustomer) {
            $this->notifier->sendTemplate('order_refunded', [
                'name' => $order->buyer_name, 'order_ref' => $order->order_ref,
                'amount' => jm_rupiah($amount), 'reason' => $reason,
            ], $order->buyer_phone, $order->buyer_email);
        }

        $this->outbound->dispatch('order.refunded', [
            'order_ref' => $order->order_ref, 'amount' => $amount, 'reason' => $reason,
        ]);

        return $refund;
    }
}
