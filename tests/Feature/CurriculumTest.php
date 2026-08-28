<?php

namespace Tests\Feature;

use App\Models\Course;
use App\Models\CourseCategory;
use App\Models\CurriculumSection;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class CurriculumTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_add_lesson_with_fee_per_meeting(): void
    {
        Role::create(['name' => 'admin']);
        $admin = User::factory()->create(['is_active' => true]);
        $admin->assignRole('admin');

        $category = CourseCategory::create([
            'name' => 'Robotics',
            'slug' => 'robotics',
            'is_active' => true,
        ]);
        $course = Course::create([
            'course_category_id' => $category->id,
            'name' => 'Game Level 2',
            'code' => 'GI-01',
            'is_active' => true,
        ]);
        $section = CurriculumSection::create([
            'course_id' => $course->id,
            'name' => 'Fundamental',
        ]);

        $this->actingAs($admin)
            ->post(route('curriculum.lessons.store', $section), [
                'title' => 'Game Logic',
                'meetings' => 2,
                'fee_per_meeting' => 150000,
                'sort_order' => 1,
            ])
            ->assertRedirect()
            ->assertSessionHasNoErrors();

        $this->assertDatabaseHas('curriculum_lessons', [
            'curriculum_section_id' => $section->id,
            'title' => 'Game Logic',
            'meetings' => 2,
            'fee_per_meeting' => 150000,
        ]);

        $this->actingAs($admin)
            ->get(route('courses.curriculum', $course))
            ->assertOk()
            ->assertSee('Rp 150.000/pertemuan');
    }

    public function test_fee_per_meeting_is_required_and_cannot_be_negative(): void
    {
        Role::create(['name' => 'admin']);
        $admin = User::factory()->create(['is_active' => true]);
        $admin->assignRole('admin');

        $category = CourseCategory::create([
            'name' => 'Robotics',
            'slug' => 'robotics',
            'is_active' => true,
        ]);
        $course = Course::create([
            'course_category_id' => $category->id,
            'name' => 'Game Level 2',
            'code' => 'GI-01',
            'is_active' => true,
        ]);
        $section = CurriculumSection::create([
            'course_id' => $course->id,
            'name' => 'Fundamental',
        ]);

        $this->actingAs($admin)
            ->from(route('courses.curriculum', $course))
            ->post(route('curriculum.lessons.store', $section), [
                'title' => 'Game Logic',
                'meetings' => 1,
                'fee_per_meeting' => -1,
                'sort_order' => 1,
            ])
            ->assertRedirect(route('courses.curriculum', $course))
            ->assertSessionHasErrors('fee_per_meeting');
    }
}
