<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;
use Throwable;

class GoogleAuthController extends Controller
{
    public function redirect(Request $request): RedirectResponse
    {
        if (! $this->isConfigured()) {
            return to_route($request->filled('role') ? 'register' : 'login')
                ->withErrors(['google' => 'Google SSO belum dikonfigurasi oleh administrator.']);
        }

        $role = $request->string('role')->toString();
        session(['google_registration_role' => in_array($role, ['guru', 'student'], true) ? $role : null]);

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

        $user = User::where('google_id', $googleUser->getId())->orWhere('email', $email)->first();
        if (! $user) {
            $role = session()->pull('google_registration_role');
            if (! in_array($role, ['guru', 'student'], true)) {
                return to_route('register')->withErrors([
                    'google' => 'Akun belum terdaftar. Pilih daftar sebagai Guru atau Student melalui Google.',
                ]);
            }

            $user = DB::transaction(function () use ($googleUser, $email, $role) {
                $user = User::create([
                    'name' => $googleUser->getName() ?: Str::before($email, '@'),
                    'email' => $email,
                    'email_verified_at' => now(),
                    'google_id' => $googleUser->getId(),
                    'google_avatar' => $googleUser->getAvatar(),
                    'password' => Hash::make(Str::random(48)),
                ]);
                $user->assignRole($role);

                return $user;
            });
        } else {
            if (! $user->is_active) {
                return to_route('login')->withErrors(['email' => 'Akun Anda sedang dinonaktifkan.']);
            }

            $user->forceFill([
                'google_id' => $googleUser->getId(),
                'google_avatar' => $googleUser->getAvatar(),
                'email_verified_at' => $user->email_verified_at ?: now(),
            ])->save();
            session()->forget('google_registration_role');
        }

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
