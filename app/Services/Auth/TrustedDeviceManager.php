<?php

namespace App\Services\Auth;

use App\Models\ActivityLog;
use App\Models\User;
use App\Models\UserTrustedDevice;
use Illuminate\Support\Str;

/**
 * "Ingat perangkat ini 30 hari": lolos 2FA + centang -> cookie token acak
 * (hash disimpan). Kunjungan berikutnya di perangkat sama melewati challenge.
 * Bisa dicabut per perangkat dari halaman Perangkat & Sesi.
 */
class TrustedDeviceManager
{
    public const COOKIE = 'jvm_trusted_device';
    public const DAYS = 30;

    public function trust(User $user): void
    {
        $token = Str::random(64);
        UserTrustedDevice::create([
            'user_id'    => $user->id,
            'token_hash' => hash('sha256', $token),
            'user_agent' => mb_substr((string) request()->userAgent(), 0, 250),
            'ip'         => request()->ip(),
            'expires_at' => now()->addDays(self::DAYS),
        ]);
        cookie()->queue(cookie(self::COOKIE, $token, self::DAYS * 24 * 60, secure: true, httpOnly: true));
        ActivityLog::record('2fa.device_trusted', $user);
    }

    public function isTrusted(User $user): bool
    {
        $token = request()->cookie(self::COOKIE);
        if (! $token) return false;
        return UserTrustedDevice::where('user_id', $user->id)
            ->where('token_hash', hash('sha256', $token))
            ->where('expires_at', '>', now())->exists();
    }

    public function revoke(User $user, int $deviceId): void
    {
        UserTrustedDevice::where('user_id', $user->id)->where('id', $deviceId)->delete();
        ActivityLog::record('2fa.device_revoked', $user);
    }

    public function revokeAll(User $user): void
    {
        UserTrustedDevice::where('user_id', $user->id)->delete();
        ActivityLog::record('2fa.devices_revoked_all', $user);
    }
}
