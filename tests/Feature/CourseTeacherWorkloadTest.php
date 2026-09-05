<?php

namespace Tests\Feature;

use App\Models\Course;
use App\Models\CourseCategory;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class CourseTeacherWorkloadTest extends TestCase
{
    use RefreshDatabase;

    public function test_course_form_shows_workload_and_prioritizes_unassigned_teacher(): void
    {
        Role::create(['name' => 'admin']);
        Role::create(['name' => 'guru']);
        $admin = User::factory()->create();
        $admin->assignRole('admin');
        $availableTeacher = User::factory()->create(['name' => 'Guru Available']);
        $availableTeacher->assignRole('guru');
        $busyTeacher = User::factory()->create(['name' => 'Guru Busy']);
        $busyTeacher->assignRole('guru');
        $category = CourseCategory::create([
            'name' => 'Programming', 'slug' => 'programming', 'is_active' => true,
        ]);
        $course = Course::create([
            'course_category_id' => $category->id,
            'name' => 'Existing Course', 'code' => 'CRS-5001', 'is_active' => true,
        ]);
        $course->teachers()->attach($busyTeacher);

        $this->actingAs($admin)
            ->get(route('courses.create'))
            ->assertOk()
            ->assertSee('[BELUM MENGAJAR]')
            ->assertSee('[MENGAJAR 1 COURSE]')
            ->assertSeeInOrder(['Guru Available', 'Guru Busy']);
    }
}
