<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\ProductReview;
use App\Services\LMS\MemberAccessGrantor;
use Illuminate\Http\Request;

class ReviewController extends Controller
{
    /** Hanya pembeli (punya akses) yang bisa mengulas; moderasi admin sebelum tampil. */
    public function store(Request $request, string $slug)
    {
        $product = Product::published()->where('slug', $slug)->firstOrFail();
        abort_unless(
            MemberAccessGrantor::hasActiveAccess($request->user(), $product->id),
            403, 'Hanya pembeli produk ini yang dapat memberi ulasan.'
        );

        $data = $request->validate([
            'rating' => 'required|integer|min:1|max:5',
            'body'   => 'nullable|string|max:1000',
        ]);

        ProductReview::updateOrCreate(
            ['product_id' => $product->id, 'user_id' => $request->user()->id],
            ['rating' => $data['rating'], 'body' => $data['body'], 'status' => 'pending']
        );
        return back()->with('status', 'Terima kasih! Ulasan Anda tampil setelah dimoderasi admin.');
    }
}
