<?php

namespace App\Http\Middleware;

use App\Services\Auth\TrustedDeviceManager;
use App\Services\Auth\TwoFactorManager;
use Closure;
use Illuminate\Http\Request;

/**
 * Admin panel terkunci sampai 2FA lolos:
 * - super_admin belum enroll -> paksa ke halaman enrollment
 * - sudah enroll tapi sesi belum verified -> paksa ke halaman challenge
 */
class RequireTwoFactor
{
    public function __construct(
        protected TwoFactorManager $twoFactor,
        protected TrustedDeviceManager $trustedDevices,
    ) {}

    public function handle(Request $request, Closure $next)
    {
        $user = $request->user();
        if (! $user || ! in_array($user->role, ['super_admin', 'staff'], true)) {
            abort(403);
        }

        // Staff: 2FA opsional kecuali dipaksa dari settings
        $staffMustEnroll = (bool) jm_setting('staff_require_2fa', false);
        $mustEnroll = $user->isSuperAdmin() || $staffMustEnroll;

        if ($mustEnroll && ! $this->twoFactor->isEnrolled($user)) {
            return redirect()->route('twofactor.enroll');
        }
        if ($this->twoFactor->isEnrolled($user) && ! $request->session()->get('2fa_verified')) {
            // Perangkat tepercaya (cookie 30 hari) melewati challenge
            if ($this->trustedDevices->isTrusted($user)) {
                $request->session()->put('2fa_verified', true);
            } else {
                return redirect()->route('twofactor.challenge');
            }
        }
        return $next($request);
    }
}
