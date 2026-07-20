<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Services\Auth\TwoFactorManager;
use Illuminate\Http\Request;

class TwoFactorController extends Controller
{
    public function __construct(protected TwoFactorManager $twoFactor) {}

    /** Enrollment dipaksa saat login pertama super_admin (redirect dari RequireTwoFactor). */
    public function showEnroll(Request $request)
    {
        $payload = $this->twoFactor->startEnrollment($request->user());
        session(['2fa_pending_secret_shown' => true]);
        return view('public.twofactor-enroll', [
            'secret'       => $payload['secret'],
            'backup_codes' => $payload['backup_codes'],   // tampil SEKALI — user wajib simpan
            'otpauth_url'  => $payload['otpauth_url'],
        ]);
    }

    public function confirmEnroll(Request $request)
    {
        $data = $request->validate(['code' => 'required|digits:6']);
        if (! $this->twoFactor->confirmEnrollment($request->user(), $data['code'])) {
            return back()->withErrors(['code' => 'Kode tidak cocok. Scan ulang QR lalu coba lagi.']);
        }
        $request->session()->put('2fa_verified', true);
        return redirect('/admin')->with('status', '2FA aktif. Simpan backup codes Anda!');
    }

    public function showChallenge()
    {
        return view('public.twofactor-challenge');
    }

    public function verifyChallenge(Request $request)
    {
        $data = $request->validate(['code' => 'required|string|max:12', 'trust_device' => 'nullable|boolean']);
        if (! $this->twoFactor->verify($request->user(), $data['code'])) {
            return back()->withErrors(['code' => 'Kode salah. Gunakan kode aplikasi authenticator atau backup code.']);
        }
        $request->session()->put('2fa_verified', true);
        if ($request->boolean('trust_device')) {
            app(\App\Services\Auth\TrustedDeviceManager::class)->trust($request->user());
        }
        return redirect()->intended('/admin');
    }
}
