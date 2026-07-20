<?php

namespace App\Http\Middleware;

use App\Support\ProcessOnVisit;
use Closure;
use Illuminate\Http\Request;

/** Dipasang di grup web publik: setelah respons dikirim, jalankan tick ringan. */
class ProcessOnVisitMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        return $next($request);
    }

    public function terminate(Request $request, $response): void
    {
        // terminate() berjalan setelah respons terkirim (FastCGI) — pengunjung tidak menunggu.
        try { ProcessOnVisit::maybeTick(); } catch (\Throwable $e) { report($e); }
    }
}
