<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;

class GoogleController extends Controller
{
    protected function configured(): bool
    {
        return filled(jm_setting('google_client_id', env('GOOGLE_CLIENT_ID')))
            && filled(jm_setting('google_client_secret', env('GOOGLE_CLIENT_SECRET')));
    }

    protected function needsProfileCompletion(User $user): bool
    {
        return blank($user->phone);
    }

    protected function applyRuntimeConfig(): void
    {
        config([
            'services.google.client_id'     => jm_setting('google_client_id', env('GOOGLE_CLIENT_ID')),
            'services.google.client_secret' => jm_setting('google_client_secret', env('GOOGLE_CLIENT_SECRET')),
            'services.google.redirect'      => url('/auth/google/callback'),
        ]);
    }

    public function redirect()
    {
        abort_unless($this->configured(), 404); // tombol juga disembunyikan di UI
        $this->applyRuntimeConfig();
        return Socialite::driver('google')->redirect();
    }

    public function callback()
    {
        abort_unless($this->configured(), 404);
        $this->applyRuntimeConfig();
        try {
            $google = Socialite::driver('google')->user();
        } catch (\Throwable) {
            return redirect('/login')->withErrors(['email' => 'Login Google gagal. Silakan coba lagi.']);
        }

        // Link ke akun existing by email, atau buat baru
        $user = User::where('google_id', $google->getId())->first()
            ?? User::where('email', strtolower((string) $google->getEmail()))->first();

        if ($user) {
            $user->update(['google_id' => $google->getId()]);
        } else {
            $user = User::create([
                'name' => $google->getName() ?: 'Pengguna Google',
                'email' => strtolower((string) $google->getEmail()),
                'google_id' => $google->getId(),
                'password' => bcrypt(str()->random(32)),
                'role' => 'customer',
            ]);
        }
        Auth::login($user, true);
        request()->session()->regenerate();

        if ($this->needsProfileCompletion($user)) {
            return redirect()->route('user.profile')->with('status', 'Lengkapi profil Anda dulu agar akun siap dipakai sepenuhnya.');
        }

        return redirect()->route('user.dashboard');
    }
}
