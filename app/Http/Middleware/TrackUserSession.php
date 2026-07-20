<?php

namespace App\Http\Middleware;

use App\Services\Auth\SessionManager;
use Closure;
use Illuminate\Http\Request;

/** Catat/refresh sesi device untuk Session Management (list + revoke). */
class TrackUserSession
{
    public function __construct(protected SessionManager $sessions) {}

    public function handle(Request $request, Closure $next)
    {
        if ($request->user()) {
            $this->sessions->track($request->user());
        }
        return $next($request);
    }
}
