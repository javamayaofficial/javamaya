<?php

namespace App\Http\Middleware;

use App\Services\Update\MaintenanceMode;
use Closure;
use Illuminate\Http\Request;

/** 503 untuk storefront + user area saat update berlangsung; admin tetap akses. */
class MaintenanceModeMiddleware
{
    public function __construct(protected MaintenanceMode $mode) {}

    public function handle(Request $request, Closure $next)
    {
        if ($this->mode->isActive() && ! $request->is('admin*') && ! $request->is('healthz')) {
            return response()->view('public.maintenance', [], 503);
        }
        return $next($request);
    }
}
