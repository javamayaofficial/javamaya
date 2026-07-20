<?php

namespace App\Http\Middleware;

use App\Models\ApiUsageLog;
use App\Services\PublicApi\ApiKeyManager;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;

/** Auth + scope + rate limit per API key untuk /api/v1/*. Param: scope wajib. */
class ApiKeyAuth
{
    public function __construct(protected ApiKeyManager $keys) {}

    public function handle(Request $request, Closure $next, string $scope)
    {
        $key = $this->keys->resolve($request->bearerToken());
        if (! $key) {
            return response()->json(['errors' => [[
                'code' => 'unauthorized',
                'message' => 'API key tidak valid. Buat API key di Admin > API Keys lalu kirim sebagai Bearer token.',
            ]]], 401);
        }
        if (! $key->hasScope($scope)) {
            return response()->json(['errors' => [[
                'code' => 'forbidden', 'message' => "API key tidak memiliki scope '$scope'.",
            ]]], 403);
        }

        $rlKey = 'apikey|' . $key->id;
        if (RateLimiter::tooManyAttempts($rlKey, (int) $key->rate_limit_per_minute)) {
            return response()->json(['errors' => [[
                'code' => 'rate_limited', 'message' => 'Rate limit API key terlampaui. Coba lagi sebentar.',
            ]]], 429);
        }
        RateLimiter::hit($rlKey, 60);

        $response = $next($request);
        ApiUsageLog::create([
            'api_key_id' => $key->id,
            'endpoint' => '/' . $request->path(),
            'method' => $request->method(),
            'status_code' => $response->getStatusCode(),
        ]);
        return $response;
    }
}
