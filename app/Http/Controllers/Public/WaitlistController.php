<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Services\Marketing\WaitlistService;
use Illuminate\Http\Request;

class WaitlistController extends Controller
{
    public function store(Request $request, string $slug, WaitlistService $waitlist)
    {
        $product = Product::published()->where('slug', $slug)->firstOrFail();
        abort_unless($product->isSoldOut(), 422, 'Produk masih tersedia.');

        $data = $request->validate([
            'name'     => 'required|string|max:100',
            'whatsapp' => 'required|string|min:9|max:20',
            'email'    => 'nullable|email|max:150',
        ]);
        $waitlist->join($product, $data['name'], $data['whatsapp'], $data['email'] ?? null);
        return back()->with('status', 'Anda masuk daftar tunggu! Kami kabari via WhatsApp begitu tersedia lagi. 🎉');
    }
}
