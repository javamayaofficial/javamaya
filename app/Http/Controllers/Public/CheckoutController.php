<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\FunnelEvent;
use App\Models\Order;
use App\Models\Product;
use App\Services\Affiliate\ReferralAttribution;
use App\Services\Checkout\CheckoutService;
use App\Services\Checkout\CouponValidator;
use App\Services\Notifications\NotificationService;
use App\Services\PaymentGateways\GatewayRegistry;
use Illuminate\Http\Request;

class CheckoutController extends Controller
{
    public function __construct(
        protected GatewayRegistry $gateways,
        protected CheckoutService $checkout,
    ) {}

    /** Halaman checkout: template dipilih per produk -> fallback global default (Checkout Template Engine). */
    public function show(string $slug)
    {
        $product = Product::published()->where('slug', $slug)->with('bumps.bumpProduct')->firstOrFail();
        $available = $this->gateways->availableForCheckout();

        if ($available->isEmpty()) {
            return view('public.checkout-unavailable', ['product' => $product]);
        }

        $template = $product->checkout_template ?: jm_setting('default_checkout_template', 'standard');
        abort_unless(in_array($template, ['minimal', 'sidebar-sticky', 'standard', 'two-step'], true), 500);

        FunnelEvent::create([
            'stage' => 'checkout_started', 'product_id' => $product->id,
            'session_hash' => hash('sha256', (string) session()->getId()), 'event_date' => today(),
        ]);

        return view("checkout.$template.index", [
            'product' => $product,
            'gateways' => $available,
        ]);
    }

    /** AJAX apply coupon (rate-limited via route middleware). */
    public function applyCoupon(Request $request, CouponValidator $validator)
    {
        $data = $request->validate([
            'slug' => 'required|string', 'code' => 'required|string|max:40', 'email' => 'nullable|email',
        ]);
        $product = Product::published()->where('slug', $data['slug'])->firstOrFail();
        $result = $validator->validate($data['code'], $product, $product->price, $data['email'] ?? null);
        return response()->json($result['ok']
            ? ['ok' => true, 'discount' => $result['discount'], 'discount_label' => jm_rupiah($result['discount'])]
            : ['ok' => false, 'message' => $result['message']]);
    }

    /** Submit checkout -> buat order -> charge via adapter -> redirect / instruksi. */
    public function store(Request $request, string $slug, ReferralAttribution $ref, NotificationService $notifier)
    {
        $product = Product::published()->where('slug', $slug)->firstOrFail();

        $data = $request->validate([
            'buyer_name'  => 'required|string|max:100',
            'buyer_email' => 'required|email|max:150',
            'buyer_phone' => 'required|string|min:9|max:20',
            'gateway'     => 'required|string|max:30',
            'coupon_code' => 'nullable|string|max:40',
            'bump_id'     => 'nullable|integer',
            'idem'        => 'required|string|size:40',   // idempotency key dari form (anti double-tap)
        ], [
            'buyer_phone.min' => 'Nomor WhatsApp tidak valid.',
        ]);

        $gateway = $this->gateways->driver($data['gateway']);
        abort_unless($gateway && $gateway->isConfigured() && \App\Models\PaymentGateway::where('code', $data['gateway'])->where('active', true)->exists(),
            422, 'Metode pembayaran tidak tersedia.');

        $order = $this->checkout->createOrder([
            'product'         => $product,
            'buyer_name'      => $data['buyer_name'],
            'buyer_email'     => $data['buyer_email'],
            'buyer_phone'     => $data['buyer_phone'],
            'gateway_code'    => $data['gateway'],
            'coupon_code'     => $data['coupon_code'] ?? null,
            'bump_id'         => $data['bump_id'] ?? null,
            'idempotency_key' => $data['idem'],
            'user_id'         => auth()->id(),
            'affiliate_slug'  => $ref->currentSlug(),
        ]);

        FunnelEvent::create([
            'stage' => 'order_created', 'product_id' => $product->id,
            'session_hash' => hash('sha256', (string) session()->getId()), 'event_date' => today(),
        ]);

        // Notifikasi instruksi bayar (WA + email) — via template, tidak memblokir bila provider kosong
        $notifier->sendTemplate('order_created', [
            'name' => $order->buyer_name, 'order_ref' => $order->order_ref,
            'total' => jm_rupiah($order->total_payable),
            'pay_url' => route('checkout.pay', $order->order_ref),
        ], $order->buyer_phone, $order->buyer_email);

        return redirect()->route('checkout.pay', $order->order_ref);
    }

    /** Halaman bayar: redirect ke gateway ATAU tampilkan instruksi transfer/QRIS. */
    public function pay(string $orderRef)
    {
        $order = Order::with('items')->where('order_ref', $orderRef)->firstOrFail();
        if ($order->isPaid())   return redirect()->route('checkout.thankyou', $order->order_ref);
        if ($order->status === 'expired') return view('public.order-expired', ['order' => $order]);

        $gateway = $this->gateways->driver($order->gateway_code);
        abort_unless($gateway, 422, 'Metode pembayaran tidak tersedia.');

        $charge = $gateway->charge($order);
        return match ($charge['mode']) {
            'redirect'    => redirect()->away($charge['url']),
            'instruction' => view($charge['view'], ['order' => $order] + $charge['data']),
            default       => view('public.checkout-error', ['order' => $order, 'message' => $charge['message'] ?? 'Terjadi gangguan.']),
        };
    }

    public function thankYou(string $orderRef)
    {
        $order = Order::with('items')->where('order_ref', $orderRef)->firstOrFail();
        return view('public.thank-you', ['order' => $order]);
    }

    /** AJAX polling status (rate-limited): thank-you & instruksi update tanpa reload. */
    public function status(string $orderRef)
    {
        $order = Order::where('order_ref', $orderRef)->firstOrFail();
        return response()->json([
            'status' => $order->status,
            'invoice_url' => $order->isPaid() && $order->invoice_token
                ? route('invoice.show', [$order->order_ref, $order->invoice_token]) : null,
        ]);
    }
}
