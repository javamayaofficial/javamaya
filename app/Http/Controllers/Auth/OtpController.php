<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\Auth\WhatsAppOTP;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class OtpController extends Controller
{
    public function __construct(protected WhatsAppOTP $otp) {}

    public function request(Request $request)
    {
        $data = $request->validate(['phone' => 'required|string|min:9|max:20']);
        $result = $this->otp->request($data['phone']);            // rate limit 3/menit/nomor di service
        return response()->json($result, $result['ok'] ? 200 : 429);
    }

    public function verify(Request $request)
    {
        $data = $request->validate([
            'phone' => 'required|string|min:9|max:20',
            'code'  => 'required|digits:6',
            'name'  => 'nullable|string|max:100',
        ]);

        if (! $this->otp->verify($data['phone'], $data['code'])) {
            return back()->withErrors(['code' => 'Kode OTP salah atau kedaluwarsa.']);
        }

        $phone = jm_normalize_phone($data['phone']);
        $user = User::firstOrCreate(
            ['phone' => $phone],
            [
                'name' => $data['name'] ?: ('Pengguna ' . substr($phone, -4)),
                'email' => $phone . '@wa.local',
                'password' => bcrypt(str()->random(32)),
                'role' => 'customer',
            ]
        );
        Auth::login($user, true);
        $request->session()->regenerate();
        return redirect()->route('user.dashboard');
    }
}
