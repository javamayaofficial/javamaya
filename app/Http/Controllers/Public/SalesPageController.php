<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\CanvasPage;
use App\Models\FunnelEvent;

class SalesPageController extends Controller
{
    /** Tampil di URL sendiri: /l/{slug} */
    public function show(string $slug)
    {
        $page = CanvasPage::where('slug', $slug)->where('published', true)->firstOrFail();
        $this->trackView($page);
        return view('public.sales-page', ['page' => $page]);
    }

    /** Dipakai route '/' bila ada homepage sales page aktif. */
    public function homepage(CanvasPage $page)
    {
        $this->trackView($page);
        return view('public.sales-page', ['page' => $page]);
    }

    protected function trackView(CanvasPage $page): void
    {
        FunnelEvent::create([
            'stage'        => 'page_view',
            'product_id'   => null,
            'session_hash' => hash('sha256', (string) session()->getId()),
            'event_date'   => today(),
        ]);
    }
}
