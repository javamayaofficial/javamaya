<?php

namespace App\Services\PublicApi;

use App\Models\ActivityLog;
use App\Models\ApiKey;
use Illuminate\Support\Str;

class ApiKeyManager
{
    public const SCOPES = [
        'products:read', 'orders:read', 'users:read',
        'classes:read', 'coupons:read', 'affiliates:read',
    ];

    /** Key plaintext HANYA dikembalikan sekali saat dibuat. */
    public function create(string $name, array $scopes, int $rateLimit = 60): array
    {
        $plain = 'jvm_' . Str::random(40);
        $key = ApiKey::create([
            'name' => $name,
            'key_hash' => hash('sha256', $plain),
            'key_prefix' => substr($plain, 0, 10),
            'scopes' => array_values(array_intersect($scopes, self::SCOPES)),
            'rate_limit_per_minute' => max(1, min(600, $rateLimit)),
        ]);
        ActivityLog::record('api_key.created', $key);
        return ['key' => $key, 'plain' => $plain];
    }

    public function revoke(ApiKey $key): void
    {
        $key->update(['revoked_at' => now()]);
        ActivityLog::record('api_key.revoked', $key);
    }

    public function resolve(?string $bearer): ?ApiKey
    {
        if (! $bearer) return null;
        $key = ApiKey::where('key_hash', hash('sha256', $bearer))->whereNull('revoked_at')->first();
        $key?->update(['last_used_at' => now()]);
        return $key;
    }
}
