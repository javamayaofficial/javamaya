<?php

namespace App\Services\Invoice;

use App\Models\Order;
use App\Models\TaxSetting;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class InvoicePdfGenerator
{
    /** Generate invoice PDF standar (nama toko, NPWP, alamat, PPN snapshot) -> path. */
    public function generate(Order $order): string
    {
        if (! $order->invoice_number) {
            $order->invoice_number = $this->nextInvoiceNumber();
            $order->invoice_token  = Str::random(48);
            $order->save();
        }

        $tax = TaxSetting::current();
        $pdf = Pdf::loadView('invoice.pdf', [
            'order'         => $order->load('items'),
            'store_name'    => jm_setting('store_name', config('app.name')),
            'store_logo'    => jm_setting('store_logo_path'),
            'npwp'          => $tax->npwp,
            'store_address' => $tax->store_address,
        ])->setPaper('a4');

        $path = 'invoices/' . $order->invoice_number . '.pdf';
        Storage::put($path, $pdf->output());
        return $path;
    }

    protected function nextInvoiceNumber(): string
    {
        // Berurutan per bulan: INV/2026/07/00001
        $prefix = 'INV/' . now()->format('Y/m') . '/';
        $last = Order::where('invoice_number', 'like', $prefix . '%')
            ->orderByDesc('invoice_number')->value('invoice_number');
        $next = $last ? ((int) substr($last, -5)) + 1 : 1;
        return $prefix . str_pad((string) $next, 5, '0', STR_PAD_LEFT);
    }
}
