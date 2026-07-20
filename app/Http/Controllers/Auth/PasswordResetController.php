<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\Notifications\NotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * Reset password memakai NotificationService (email via Mailketing / WA via Fonnte),
 * BUKAN SMTP Laravel — konsisten dengan arsitektur provider toko.
 */
class PasswordResetController extends Controller
{
    public function showForgot()
    {
        return view('public.forgot-password');
    }

    public function sendLink(Request $request, NotificationService $notifier)
    {
        $data = $request->validate(['email' => 'required|email']);
        $user = User::where('email', strtolower($data['email']))->first();

        // Respons selalu sama (anti user-enumeration)
        $done = fn () => back()->with('status', 'Jika email terdaftar, link reset sudah dikirim (cek email & WhatsApp).');

        if (! $user) return $done();

        $token = Str::random(48);
        DB::table('password_reset_tokens')->updateOrInsert(
            ['email' => $user->email],
            ['token' => hash('sha256', $token), 'created_at' => now()]
        );

        $url = route('password.reset.form', ['token' => $token, 'email' => $user->email]);
        $message = "Halo {$user->name}, klik link berikut untuk mengatur ulang password Anda (berlaku 60 menit):\n$url";
        if ($user->email && ! str_ends_with($user->email, '@wa.local')) {
            $notifier->emailProvider()?->send($user->email, $message, 'Reset password akun Anda');
        }
        if ($user->phone) {
            $notifier->waProvider()?->send($user->phone, $message);
        }
        return $done();
    }

    public function showReset(Request $request)
    {
        return view('public.reset-password', [
            'token' => (string) $request->query('token'),
            'email' => (string) $request->query('email'),
        ]);
    }

    public function reset(Request $request)
    {
        $data = $request->validate([
            'email' => 'required|email',
            'token' => 'required|string',
            'password' => 'required|string|min:8|confirmed',
        ]);

        $row = DB::table('password_reset_tokens')->where('email', strtolower($data['email']))->first();
        $valid = $row
            && hash_equals($row->token, hash('sha256', $data['token']))
            && now()->parse($row->created_at)->gt(now()->subMinutes(60));

        if (! $valid) {
            return back()->withErrors(['email' => 'Link reset tidak valid atau kedaluwarsa. Minta link baru.']);
        }

        User::where('email', strtolower($data['email']))->update(['password' => Hash::make($data['password'])]);
        DB::table('password_reset_tokens')->where('email', strtolower($data['email']))->delete();

        return redirect()->route('login')->with('status', 'Password berhasil diubah. Silakan masuk.');
    }
}
