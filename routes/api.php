<?php

use App\Http\Controllers\Api\V1\OrdersController;
use App\Http\Controllers\Api\V1\ProductsController;
use Illuminate\Support\Facades\Route;

/*
| Public REST API v1. Auth: Bearer API key (Admin > API Keys), scope per key,
| rate limit per key. Tanpa key -> 401 dengan pesan jelas (di middleware).
*/
Route::prefix('v1')->group(function () {
    Route::get('/products', [ProductsController::class, 'index'])->middleware('jm.apikey:products:read');
    Route::get('/orders', [OrdersController::class, 'index'])->middleware('jm.apikey:orders:read');
    Route::get('/orders/{orderRef}', [OrdersController::class, 'show'])->middleware('jm.apikey:orders:read');
});
