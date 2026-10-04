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

    public function test_google_cannot_create_an_account_even_with_a_registration_role(): void
    {
        Role::findOrCreate('student');
        $this->mockGoogleUser('google-123', 'google@example.com', 'Google Student');

        $this->withSession(['google_registration_role' => 'student'])
            ->get(route('google.callback'))
            ->assertRedirect(route('register'))
            ->assertSessionHasErrors('google');

        $this->assertDatabaseMissing('users', ['email' => 'google@example.com']);
        $this->assertGuest();
    }

    public function test_google_login_links_an_existing_account_by_verified_email(): void
    {
        Role::findOrCreate('guru');
        $user = User::factory()->create(['email' => 'Guru-Google@example.com']);
        $user->assignRole('guru');
        $this->mockGoogleUser('google-guru', strtolower($user->email), 'Guru Google');

        $this->get(route('google.callback'))->assertRedirect(route('home'));

        $this->assertAuthenticatedAs($user);
        $this->assertSame('google-guru', $user->fresh()->google_id);
        $this->assertTrue($user->fresh()->hasRole('guru'));
    }

    public function test_unverified_google_email_cannot_link_an_account(): void
    {
        $user = User::factory()->create(['email' => 'unverified@example.com']);
        $this->mockGoogleUser('unverified-google', $user->email, 'Unverified', false);
        $this->get(route('google.callback'))->assertRedirect(route('login'))->assertSessionHasErrors('google');
        $this->assertGuest();
        $this->assertNull($user->fresh()->google_id);
    }

    public function test_registration_has_no_google_signup_buttons(): void
    {
        $this->get(route('register'))->assertOk()
            ->assertDontSee('Google sebagai Guru')
            ->assertDontSee('Google sebagai Student')
            ->assertSee('alamat email aktif');
    }

    public function test_registration_normalizes_email_and_rejects_invalid_email(): void
    {
        Role::findOrCreate('student');
        $action = app(\App\Actions\Fortify\CreateNewUser::class);
        $data = [
            'name' => 'Siswa', 'email' => '  Siswa.Google@Gmail.com  ',
            'password' => 'PasswordBaru123!', 'password_confirmation' => 'PasswordBaru123!',
            'role' => 'student', 'phone' => '081234567890', 'address' => 'Jakarta',
        ];
        $user = $action->create($data);
        $this->assertSame('siswa.google@gmail.com', $user->email);
        $this->assertNull($user->email_verified_at);

        $this->expectException(\Illuminate\Validation\ValidationException::class);
        $action->create([...$data, 'email' => 'alamat-bukan-email']);
    }

    private function mockGoogleUser(string $id, string $email, string $name, bool $verified = true): void
    {
        $googleUser = (new GoogleUser())->setRaw(['email_verified' => $verified])->map([
            'id' => $id, 'email' => $email, 'name' => $name,
            'avatar' => 'https://example.com/avatar.jpg',
        ]);
        $provider = Mockery::mock(Provider::class);
        $provider->shouldReceive('user')->once()->andReturn($googleUser);
        Socialite::shouldReceive('driver')->with('google')->once()->andReturn($provider);
    }
}
