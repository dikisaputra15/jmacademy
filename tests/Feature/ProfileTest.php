<?php

namespace Tests\Feature;

use App\Models\CourseCategory;
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
            'regency_id' => '36.73',
            'regency_name' => 'KOTA SERANG',
            'regency_id' => '3673',
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

    public function test_teacher_can_update_bank_information_and_choose_multiple_specializations(): void
    {
        Role::create(['name' => 'guru']);
        $teacher = User::factory()->create();
        $teacher->assignRole('guru');
        $programming = CourseCategory::create([
            'name' => 'Programming', 'slug' => 'programming', 'is_active' => true,
        ]);
        $robotics = CourseCategory::create([
            'name' => 'Robotics', 'slug' => 'robotics', 'is_active' => true,
        ]);

        $this->actingAs($teacher)
            ->get(route('profile.edit'))
            ->assertOk()
            ->assertSee('Data Pengajar')
            ->assertSee('Programming')
            ->assertSee('Robotics');

        $this->actingAs($teacher)->put(route('profile.update'), [
            'name' => $teacher->name,
            'email' => $teacher->email,
            'bank_name' => 'BCA',
            'bank_account_number' => '1234567890',
            'bank_account_holder' => 'Guru Academy',
            'teaching_category_ids' => [$programming->id, $robotics->id],
        ])->assertRedirect(route('profile.edit'))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('users', [
            'id' => $teacher->id,
            'bank_name' => 'BCA',
            'bank_account_number' => '1234567890',
            'bank_account_holder' => 'Guru Academy',
        ]);
        $this->assertDatabaseHas('course_category_teacher', [
            'user_id' => $teacher->id,
            'course_category_id' => $programming->id,
        ]);
        $this->assertDatabaseHas('course_category_teacher', [
            'user_id' => $teacher->id,
            'course_category_id' => $robotics->id,
        ]);
    }

    public function test_non_teacher_cannot_update_teacher_bank_information(): void
    {
        $user = User::factory()->create();
        $category = CourseCategory::create([
            'name' => 'Programming', 'slug' => 'programming', 'is_active' => true,
        ]);

        $this->actingAs($user)->put(route('profile.update'), [
            'name' => $user->name,
            'email' => $user->email,
            'bank_name' => 'BCA',
            'bank_account_number' => '1234567890',
            'bank_account_holder' => 'Not A Teacher',
            'teaching_category_ids' => [$category->id],
        ])->assertRedirect(route('profile.edit'));

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'bank_name' => null,
            'bank_account_number' => null,
            'bank_account_holder' => null,
        ]);
        $this->assertDatabaseMissing('course_category_teacher', [
            'user_id' => $user->id,
        ]);
    }
}
