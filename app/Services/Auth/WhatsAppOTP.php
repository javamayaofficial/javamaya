<?php

namespace App\Services\Auth;

use App\Models\OtpCode;
use App\Services\Notifications\NotificationService;
use Illuminate\Support\Facades\Hash;

/** WA OTP: 6 digit, rate limit 3/menit/nomor, expiry 5 menit, max 5 verify attempt. */
class WhatsAppOTP
{
    public function __construct(protected NotificationService $notifier) {}

    /** @return array{ok: bool, message: string} */
    public function request(string $phone, string $purpose = 'login'): array
    {
        $phone = jm_normalize_phone($phone);

        $recentCount = OtpCode::where('phone', $phone)
            ->where('created_at', '>=', now()->subMinute())->count();
        if ($recentCount >= (int) config('javamaya.otp.max_per_minute')) {
            return ['ok' => false, 'message' => 'Terlalu banyak permintaan OTP. Coba lagi 1 menit lagi.'];
        }

        if (! app(NotificationService::class)->waProvider()) {
            return ['ok' => false, 'message' => 'Login OTP belum tersedia. Gunakan email & password.'];
        }

        $code = (string) random_int(100000, 999999);
        OtpCode::create([
            'phone' => $phone, 'code_hash' => Hash::make($code), 'purpose' => $purpose,
            'expires_at' => now()->addMinutes((int) config('javamaya.otp.ttl_minutes')),
        ]);

        $this->notifier->sendChannel('wa', 'otp_code', ['code' => $code, 'minutes' => config('javamaya.otp.ttl_minutes')], $phone);
        return ['ok' => true, 'message' => 'Kode OTP dikirim ke WhatsApp Anda.'];
    }

    public function verify(string $phone, string $code, string $purpose = 'login'): bool
    {
        $phone = jm_normalize_phone($phone);
        $otp = OtpCode::where('phone', $phone)->where('purpose', $purpose)
            ->whereNull('used_at')->where('expires_at', '>', now())
            ->latest()->first();
        if (! $otp) return false;

        if ($otp->attempts >= (int) config('javamaya.otp.max_verify_attempts')) return false;
        $otp->increment('attempts');

        if (! Hash::check($code, $otp->code_hash)) return false;
        $otp->update(['used_at' => now()]);
        return true;
    }
}
