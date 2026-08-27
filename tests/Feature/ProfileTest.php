<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class ProfileTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_cannot_open_profile(): void
    {
        $this->get(route('profile.edit'))->assertRedirect(route('login'));
    }

    public function test_authenticated_user_can_update_own_profile(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->put(route('profile.update'), [
            'name' => 'Ayu Diah',
            'email' => 'ayu@example.com',
            'phone' => '081234567890',
            'gender' => 'female',
            'date_of_birth' => '2000-01-15',
            'province_id' => '36',
            'province_name' => 'BANTEN',
            'regency_id' => '3673',
            'regency_name' => 'KOTA SERANG',
            'postal_code' => '42171',
            'address' => 'Jl. Pendidikan No. 1',
        ])->assertRedirect(route('profile.edit'))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'name' => 'Ayu Diah',
            'province_name' => 'BANTEN',
            'regency_name' => 'KOTA SERANG',
        ]);
    }

    public function test_region_endpoint_normalizes_api_response(): void
    {
        Http::fake([
            'https://wilayah.id/api/provinces.json' => Http::response([
                'data' => [['code' => '36', 'name' => 'BANTEN']],
            ]),
        ]);

        $user = User::factory()->create();

        $this->actingAs($user)
            ->getJson(route('regions.index', ['level' => 'provinces']))
            ->assertOk()
            ->assertJsonPath('data.0.name', 'BANTEN');
    }

    public function test_student_can_update_parent_information(): void
    {
        Role::create(['name' => 'student']);
        $student = User::factory()->create();
        $student->assignRole('student');

        $this->actingAs($student)->put(route('profile.update'), [
            'name' => $student->name,
            'email' => $student->email,
            'parent_name' => 'Budi Santoso',
            'parent_phone' => '081298765432',
        ])->assertRedirect(route('profile.edit'));

        $this->assertDatabaseHas('users', [
            'id' => $student->id,
            'parent_name' => 'Budi Santoso',
            'parent_phone' => '081298765432',
        ]);
    }
}
