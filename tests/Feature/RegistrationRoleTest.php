<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class RegistrationRoleTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_register_as_student(): void
    {
        Role::create(['name' => 'student']);

        $this->post(route('register'), [
            'role' => 'student',
            'name' => 'Student Baru',
            'email' => 'student.baru@example.com',
            'phone' => '081234567890',
            'address' => 'Jl. Pendidikan No. 1',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ])->assertRedirect('/home');

        $user = User::where('email', 'student.baru@example.com')->firstOrFail();
        $this->assertTrue($user->hasRole('student'));
        $this->assertSame('081234567890', $user->phone);
        $this->assertSame('Jl. Pendidikan No. 1', $user->address);
    }

    public function test_user_can_register_as_teacher(): void
    {
        Role::create(['name' => 'guru']);

        $this->post(route('register'), [
            'role' => 'guru',
            'name' => 'Guru Baru',
            'email' => 'guru.baru@example.com',
            'phone' => '081298765432',
            'address' => 'Jl. Guru No. 2',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ])->assertRedirect('/home');

        $this->assertTrue(User::where('email', 'guru.baru@example.com')->firstOrFail()->hasRole('guru'));
    }

    public function test_public_registration_cannot_create_admin(): void
    {
        foreach (['admin', 'guru', 'student'] as $role) {
            Role::create(['name' => $role]);
        }

        $this->post(route('register'), [
            'role' => 'admin',
            'name' => 'Fake Admin',
            'email' => 'fake.admin@example.com',
            'phone' => '081234567890',
            'address' => 'Alamat',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ])->assertSessionHasErrors('role');

        $this->assertDatabaseMissing('users', ['email' => 'fake.admin@example.com']);
    }
}
