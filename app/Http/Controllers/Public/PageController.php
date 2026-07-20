<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Certificate;
use App\Models\ContentPage;
use App\Models\Order;
use App\Services\ContentCMS\StaticPageRenderer;

class PageController extends Controller
{
    public function contentPage(string $slug, StaticPageRenderer $renderer)
    {
        $page = ContentPage::where('slug', $slug)->where('published', true)->firstOrFail();
        return view('public.static-page', ['page' => $page, 'html' => $renderer->render($page)]);
    }

    public function certificateVerify(string $code)
    {
        $certificate = Certificate::with('classRoom')->where('code', strtoupper(trim($code)))->first();
        return view('public.certificate-verify', ['certificate' => $certificate, 'code' => $code]);
    }

    public function invoice(string $orderRef, string $token)
    {
        $order = Order::where('order_ref', $orderRef)->where('invoice_token', $token)->firstOrFail();
        $path = storage_path('app/invoices/' . $order->invoice_number . '.pdf');
        // Regenerate bila file hilang (mis. pasca-migrasi hosting)
        if (! is_file($path)) {
            app(\App\Services\Invoice\InvoicePdfGenerator::class)->generate($order);
        }
        return response()->file($path, ['Content-Type' => 'application/pdf']);
    }

    public function healthz(\App\Services\Health\HealthChecker $checker)
    {
        $result = $checker->check();
        return response()->json($result['body'], $result['status']);
    }
}
