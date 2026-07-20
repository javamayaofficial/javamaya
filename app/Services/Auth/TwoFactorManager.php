<?php

namespace App\Services\Auth;

use App\Models\ActivityLog;
use App\Models\User;
use App\Models\UserTwoFactor;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use PragmaRX\Google2FA\Google2FA;

/** 2FA admin TOTP + 8 backup codes (hashed, sekali pakai, bisa regenerate). */
class TwoFactorManager
{
    public function __construct(protected Google2FA $google2fa) {}

    /** Mulai enrollment: buat secret + backup codes plaintext SEKALI tampil. */
    public function startEnrollment(User $user): array
    {
        $secret = $this->google2fa->generateSecretKey();
        $plainCodes = collect(range(1, 8))->map(fn () => strtoupper(Str::random(10)))->all();

        UserTwoFactor::updateOrCreate(
            ['user_id' => $user->id],
            ['secret' => $secret, 'backup_codes' => array_map(fn ($c) => Hash::make($c), $plainCodes), 'confirmed_at' => null]
        );

        $otpauth = $this->google2fa->getQRCodeUrl(
            jm_setting('store_name', config('app.name')), $user->email, $secret
        );
        return ['secret' => $secret, 'backup_codes' => $plainCodes, 'otpauth_url' => $otpauth];
    }

    public function confirmEnrollment(User $user, string $code): bool
    {
        $tf = $user->twoFactor;
        if (! $tf) return false;
        if (! $this->google2fa->verifyKey($tf->secret, $code)) return false;
        $tf->update(['confirmed_at' => now()]);
        ActivityLog::record('2fa.enrolled', $user);
        return true;
    }

    public function verify(User $user, string $code): bool
    {
        $tf = $user->twoFactor;
        if (! $tf || ! $tf->confirmed_at) return false;

        if ($this->google2fa->verifyKey($tf->secret, $code)) return true;

        // Backup code: sekali pakai
        $codes = $tf->backup_codes ?? [];
        foreach ($codes as $i => $hashed) {
            if ($hashed && Hash::check(strtoupper(trim($code)), $hashed)) {
                $codes[$i] = null;
                $tf->update(['backup_codes' => $codes]);
                ActivityLog::record('2fa.backup_code_used', $user);
                return true;
            }
        }
        return false;
    }

    public function isEnrolled(User $user): bool
    {
        return (bool) $user->twoFactor?->confirmed_at;
    }
}
