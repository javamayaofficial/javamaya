<?php

namespace App\Http\Middleware;

use App\Models\RateLimitLog;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;

/**
 * Rate limit universal untuk public POST: default 60/menit/IP,
 * konfigurable per route via Settings key "rate_limit.{routeName}".
 */
class RateLimitPublic
{
    public function handle(Request $request, Closure $next, ?string $maxOverride = null)
    {
        $routeName = $request->route()?->getName() ?? $request->path();
        $max = (int) ($maxOverride
            ?? jm_setting('rate_limit.' . $routeName)
            ?? config('javamaya.rate_limit.public_post_per_minute'));

        $key = 'rlp|' . $routeName . '|' . $request->ip();
        if (RateLimiter::tooManyAttempts($key, $max)) {
            RateLimitLog::create(['route' => $routeName, 'ip' => $request->ip()]);
            return response()->json([
                'message' => 'Terlalu banyak permintaan. Silakan coba lagi dalam satu menit.',
            ], 429);
        }
        RateLimiter::hit($key, 60);
        return $next($request);
    }
}
