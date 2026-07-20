<?php

namespace App\Services\Checkout;

use App\Models\Order;
use App\Models\Payment;
use App\Services\Affiliate\CommissionCalculator;
use App\Services\Invoice\InvoicePdfGenerator;
use App\Services\LMS\MemberAccessGrantor;
use App\Services\Notifications\NotificationService;
use App\Services\OutboundWebhook\EventDispatcher;
use Illuminate\Support\Facades\DB;

/**
 * SATU-SATUNYA jalur untuk menandai order lunas.
 * Dipanggil dari webhook processor (Duitku/Xendit/Moota) dan konfirmasi manual admin.
 * Idempotent: order yang sudah paid tidak diproses ulang.
 */
class OrderPaidPipeline
{
    public function __construct(
        protected MemberAccessGrantor $accessGrantor,
        protected InvoicePdfGenerator $invoice,
        protected NotificationService $notifier,
        protected EventDispatcher $outbound,
        protected CommissionCalculator $commission,
    ) {}

    public function markPaid(Order $order, string $gatewayReference = null, array $rawPayload = []): void
    {
        if ($order->isPaid()) return; // idempotent

        DB::transaction(function () use ($order, $gatewayReference, $rawPayload) {
            $order->update(['status' => 'paid', 'paid_at' => now()]);
            Payment::create([
                'order_id' => $order->id, 'gateway_code' => $order->gateway_code,
                'gateway_reference' => $gatewayReference, 'amount' => $order->total_payable,
                'status' => 'success', 'raw_payload' => $rawPayload,
            ]);
            $this->accessGrantor->grantForOrder($order);      // member_access per item (bundle di-expand)
            $this->commission->recordForOrder($order);        // komisi affiliate bila ada ref
        });

        // Di luar transaksi DB (side effects boleh gagal tanpa membatalkan status paid):
        $invoicePath = $this->invoice->generate($order->fresh());
        $this->notifier->sendTemplate('payment_confirmed', [
            'name'        => $order->buyer_name,
            'order_ref'   => $order->order_ref,
            'total'       => jm_rupiah($order->total_payable),
            'invoice_url' => route('invoice.show', [$order->order_ref, $order->fresh()->invoice_token]),
            'member_url'  => route('user.dashboard'),
        ], $order->buyer_phone, $order->buyer_email);

        // Enroll email drip sequence (trigger: purchase_product per item)
        foreach ($order->items as $item) {
            app(\App\Services\Marketing\EmailSequenceProcessor::class)
                ->enroll($order->buyer_email, 'purchase_product', $item->product_id);
        }

        $this->outbound->dispatch('order.completed', [
            'order_ref' => $order->order_ref,
            'buyer'     => ['name' => $order->buyer_name, 'email' => $order->buyer_email, 'phone' => $order->buyer_phone],
            'total'     => $order->total_payable,
            'items'     => $order->items->map(fn ($i) => ['product' => $i->product_name, 'price' => $i->price])->all(),
            'paid_at'   => $order->paid_at?->toIso8601String(),
        ]);
    }
}
