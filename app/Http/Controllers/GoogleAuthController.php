<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;
use Throwable;

class GoogleAuthController extends Controller
{
    public function redirect(Request $request): RedirectResponse
    {
        if (! $this->isConfigured()) {
            return to_route('login')
                ->withErrors(['google' => 'Google SSO belum dikonfigurasi oleh administrator.']);
        }

        session()->forget('google_registration_role');

        return Socialite::driver('google')->redirect();
    }

    public function callback(): RedirectResponse
    {
        try {
            $googleUser = Socialite::driver('google')->user();
        } catch (Throwable) {
            return to_route('login')->withErrors(['google' => 'Autentikasi Google gagal atau dibatalkan. Silakan coba lagi.']);
        }

        $email = Str::lower((string) $googleUser->getEmail());
        if ($email === '') {
            return to_route('login')->withErrors(['google' => 'Akun Google tidak memberikan alamat email.']);
        }

        if (($googleUser->user['email_verified'] ?? false) !== true || ! filled($googleUser->getId())) {
            return to_route('login')->withErrors(['google' => 'Alamat email akun Google belum terverifikasi.']);
        }

        $user = User::where('google_id', $googleUser->getId())->first()
            ?? User::whereRaw('LOWER(email) = ?', [$email])->first();

        if (! $user) {
            return to_route('register')->withErrors([
                'google' => 'Akun belum terdaftar. Isi formulir pendaftaran menggunakan alamat email yang sama dengan akun Google Anda.',
            ]);
        }

        if (! $user->is_active) {
            return to_route('login')->withErrors(['email' => 'Akun Anda sedang dinonaktifkan.']);
        }

        if ($user->google_id && $user->google_id !== $googleUser->getId()) {
            return to_route('login')->withErrors(['google' => 'Akun ini sudah terhubung dengan akun Google lain.']);
        }

        $user->forceFill([
            'google_id' => $googleUser->getId(),
            'google_avatar' => $googleUser->getAvatar(),
            'email_verified_at' => $user->email_verified_at ?: now(),
        ])->save();
        session()->forget('google_registration_role');

        Auth::login($user, true);
        request()->session()->regenerate();

        return redirect()->intended(route('home'));
    }

    private function isConfigured(): bool
    {
        return filled(config('services.google.client_id'))
            && filled(config('services.google.client_secret'))
            && filled(config('services.google.redirect'));
    }
}
