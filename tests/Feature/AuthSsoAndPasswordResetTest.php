<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Laravel\Socialite\Contracts\Provider;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\User as GoogleUser;
use Mockery;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class AuthSsoAndPasswordResetTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_can_request_and_use_password_reset_link(): void
    {
        Notification::fake();
        $user = User::factory()->create(['email' => 'reset@example.com']);

        $this->get(route('password.request'))
            ->assertOk()->assertSee('Kirim Link Reset Password');
        $this->post(route('password.email'), ['email' => $user->email])
            ->assertSessionHas('status');

        $token = null;
        Notification::assertSentTo($user, ResetPassword::class, function ($notification) use (&$token) {
            $token = $notification->token;
            return true;
        });

        $this->get(route('password.reset', ['token' => $token, 'email' => $user->email]))
            ->assertOk()->assertSee('Buat Password Baru');
        $this->post(route('password.update'), [
            'token' => $token, 'email' => $user->email,
            'password' => 'PasswordBaru123!', 'password_confirmation' => 'PasswordBaru123!',
        ])->assertRedirect(route('home'));

        $this->assertTrue(Hash::check('PasswordBaru123!', $user->fresh()->password));
    }

    public function test_google_registration_creates_user_with_selected_role(): void
    {
        Role::findOrCreate('student');
        $this->mockGoogleUser('google-123', 'google@example.com', 'Google Student');

        $this->withSession(['google_registration_role' => 'student'])
            ->get(route('google.callback'))
            ->assertRedirect(route('home'));

        $user = User::where('email', 'google@example.com')->firstOrFail();
        $this->assertSame('google-123', $user->google_id);
        $this->assertTrue($user->hasRole('student'));
        $this->assertAuthenticatedAs($user);
    }

    public function test_google_login_links_an_existing_account_by_verified_email(): void
    {
        Role::findOrCreate('guru');
        $user = User::factory()->create(['email' => 'guru-google@example.com']);
        $user->assignRole('guru');
        $this->mockGoogleUser('google-guru', $user->email, 'Guru Google');

        $this->get(route('google.callback'))->assertRedirect(route('home'));

        $this->assertAuthenticatedAs($user);
        $this->assertSame('google-guru', $user->fresh()->google_id);
        $this->assertTrue($user->fresh()->hasRole('guru'));
    }

    private function mockGoogleUser(string $id, string $email, string $name): void
    {
        $googleUser = (new GoogleUser())->map([
            'id' => $id, 'email' => $email, 'name' => $name,
            'avatar' => 'https://example.com/avatar.jpg',
        ]);
        $provider = Mockery::mock(Provider::class);
        $provider->shouldReceive('user')->once()->andReturn($googleUser);
        Socialite::shouldReceive('driver')->with('google')->once()->andReturn($provider);
    }
}
