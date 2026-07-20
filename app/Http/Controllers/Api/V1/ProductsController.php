<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\Request;

class ProductsController extends Controller
{
    public function index(Request $request)
    {
        $products = Product::published()
            ->when($request->query('type'), fn ($q, $t) => $q->where('type', $t))
            ->latest()->paginate(min(100, (int) $request->query('per_page', 20)));

        return response()->json([
            'data' => $products->map(fn ($p) => [
                'id' => $p->id, 'name' => $p->name, 'slug' => $p->slug, 'type' => $p->type,
                'price' => $p->price, 'access_expiry_type' => $p->access_expiry_type,
                'url' => route('product.show', $p->slug),
            ]),
            'meta' => ['page' => $products->currentPage(), 'total' => $products->total()],
        ]);
    }
}
