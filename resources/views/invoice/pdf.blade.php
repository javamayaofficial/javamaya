<!DOCTYPE html>
<html><head><meta charset="utf-8"><style>
    body { font-family: DejaVu Sans, sans-serif; font-size: 12px; color: #1e293b; margin: 32px; }
    .header { width: 100%; border-bottom: 2px solid #1e293b; padding-bottom: 12px; }
    .store { font-size: 20px; font-weight: bold; }
    .muted { color: #64748b; }
    table.items { width: 100%; border-collapse: collapse; margin-top: 20px; }
    table.items th { text-align: left; border-bottom: 1px solid #cbd5e1; padding: 8px 4px; font-size: 11px; text-transform: uppercase; color: #64748b; }
    table.items td { border-bottom: 1px solid #e2e8f0; padding: 8px 4px; }
    .right { text-align: right; }
    .totals { width: 45%; margin-left: 55%; margin-top: 12px; }
    .totals td { padding: 4px; }
    .grand { font-size: 15px; font-weight: bold; border-top: 2px solid #1e293b; }
    .badge-paid { color: #059669; font-weight: bold; text-transform: uppercase; }
</style></head>
<body>
    <table class="header"><tr>
        <td>
            <div class="store">{{ $store_name }}</div>
            @if ($store_address)<div class="muted">{!! nl2br(e($store_address)) !!}</div>@endif
            @if ($npwp)<div class="muted">NPWP: {{ $npwp }}</div>@endif
        </td>
        <td class="right">
            <div style="font-size:18px;font-weight:bold;">INVOICE</div>
            <div>{{ $order->invoice_number }}</div>
            <div class="muted">{{ $order->paid_at?->translatedFormat('d F Y') }}</div>
            <div class="badge-paid">LUNAS</div>
        </td>
    </tr></table>

    <p style="margin-top:16px;">
        <span class="muted">Ditagihkan kepada:</span><br>
        <b>{{ $order->buyer_name }}</b><br>{{ $order->buyer_email }} • {{ $order->buyer_phone }}
    </p>

    <table class="items">
        <tr><th>Item</th><th class="right">Harga</th></tr>
        @foreach ($order->items as $item)
            <tr><td>{{ $item->product_name }}@if($item->is_bump) <span class="muted">(bump)</span>@endif</td>
                <td class="right">Rp {{ number_format($item->price, 0, ',', '.') }}</td></tr>
        @endforeach
    </table>

    <table class="totals">
        <tr><td>Subtotal</td><td class="right">Rp {{ number_format($order->subtotal, 0, ',', '.') }}</td></tr>
        @if ($order->discount > 0)
            <tr><td>Diskon</td><td class="right">- Rp {{ number_format($order->discount, 0, ',', '.') }}</td></tr>
        @endif
        @if ($order->tax_percent > 0)
            @if ($order->tax_mode === 'exclusive')
                <tr><td>PPN {{ rtrim(rtrim(number_format($order->tax_percent, 2), '0'), '.') }}%</td>
                    <td class="right">Rp {{ number_format($order->tax_amount, 0, ',', '.') }}</td></tr>
            @else
                <tr><td class="muted" colspan="2">Termasuk PPN {{ rtrim(rtrim(number_format($order->tax_percent, 2), '0'), '.') }}%
                    (Rp {{ number_format($order->tax_amount, 0, ',', '.') }})</td></tr>
            @endif
        @endif
        @if ($order->unique_code)
            <tr><td>Kode unik</td><td class="right">Rp {{ number_format($order->unique_code, 0, ',', '.') }}</td></tr>
        @endif
        <tr class="grand"><td>Total</td><td class="right">Rp {{ number_format($order->total_payable, 0, ',', '.') }}</td></tr>
    </table>

    <p class="muted" style="margin-top:40px;font-size:10px;">Invoice ini diterbitkan otomatis oleh sistem dan sah tanpa tanda tangan. Ref: {{ $order->order_ref }}</p>
</body></html>
