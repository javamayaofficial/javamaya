<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\FunnelEvent;
use App\Models\Product;
use App\Services\Affiliate\ReferralAttribution;

class ProductController extends Controller
{
    public function show(string $slug, ReferralAttribution $ref)
    {
        $product = Product::published()->where('slug', $slug)
            ->with(['reviews' => fn ($q) => $q->where('status', 'approved')->latest()->limit(10), 'bumps.bumpProduct', 'bundle.items.itemProduct'])
            ->firstOrFail();

        $ref->captureFromRequest(); // ?ref=slug -> cookie last-click

        FunnelEvent::create([
            'stage' => 'page_view', 'product_id' => $product->id,
            'session_hash' => hash('sha256', (string) session()->getId()),
            'event_date' => today(),
        ]);

        return view('public.product-detail', ['product' => $product]);
    }
}
