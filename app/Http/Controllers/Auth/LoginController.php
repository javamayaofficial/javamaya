<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\LoginAttempt;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class LoginController extends Controller
{
    public function showLogin()
    {
        $googleReady = filled(jm_setting('google_client_id', env('GOOGLE_CLIENT_ID')))
            && filled(jm_setting('google_client_secret', env('GOOGLE_CLIENT_SECRET')));
        $waReady = (bool) app(\App\Services\Notifications\NotificationService::class)->waProvider();
        return view('public.login', ['googleReady' => $googleReady, 'waReady' => $waReady]);
    }

    public function login(Request $request)
    {
        $data = $request->validate(['email' => 'required|email', 'password' => 'required|string']);

        // Throttle: 5 gagal per identifier+IP -> tunggu 5 menit
        $recentFails = LoginAttempt::where('identifier', $data['email'])->where('ip', $request->ip())
            ->where('success', false)->where('created_at', '>=', now()->subMinutes(5))->count();
        if ($recentFails >= 5) {
            return back()->withErrors(['email' => 'Terlalu banyak percobaan. Coba lagi 5 menit lagi.']);
        }

        $ok = Auth::attempt(['email' => $data['email'], 'password' => $data['password']], true);
        LoginAttempt::create(['identifier' => $data['email'], 'ip' => $request->ip(), 'success' => $ok]);
        if (! $ok) {
            return back()->withErrors(['email' => 'Email atau password salah.'])->onlyInput('email');
        }

        $request->session()->regenerate();
        $request->session()->forget('2fa_verified');
        $user = $request->user();
        return in_array($user->role, ['super_admin', 'staff'], true)
            ? redirect()->intended('/admin')
            : redirect()->intended(route('user.dashboard'));
    }

    public function register(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:100',
            'email' => 'required|email|max:150|unique:users,email',
            'phone' => 'required|string|min:9|max:20',
            'password' => 'required|string|min:8|confirmed',
        ]);
        $user = User::create($data + ['role' => 'customer', 'phone' => jm_normalize_phone($data['phone'])]);
        Auth::login($user);
        return redirect()->route('user.dashboard');
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect('/login');
    }
}
