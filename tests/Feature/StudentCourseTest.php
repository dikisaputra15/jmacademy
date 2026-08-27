<?php

namespace Tests\Feature;

use App\Models\Course;
use App\Models\CourseCategory;
use App\Models\CurriculumLesson;
use App\Models\CurriculumSection;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class StudentCourseTest extends TestCase
{
    use RefreshDatabase;

    public function test_student_sees_active_courses_and_curriculum_only(): void
    {
        Role::create(['name' => 'student']);
        $student = User::factory()->create();
        $student->assignRole('student');

        $category = CourseCategory::create([
            'name' => 'English', 'slug' => 'english', 'is_active' => true,
        ]);
        $activeCourse = Course::create([
            'course_category_id' => $category->id,
            'name' => 'English Starter',
            'code' => 'CRS-0001',
            'is_active' => true,
        ]);
        $inactiveCourse = Course::create([
            'course_category_id' => $category->id,
            'name' => 'Hidden Course',
            'code' => 'CRS-0002',
            'is_active' => false,
        ]);
        $section = CurriculumSection::create(['course_id' => $activeCourse->id, 'name' => 'Introduction']);
        CurriculumLesson::create([
            'curriculum_section_id' => $section->id,
            'title' => 'Greetings and Introductions',
            'meetings' => 2,
        ]);

        $this->actingAs($student)
            ->get(route('student-courses.index'))
            ->assertOk()
            ->assertSee('English Starter')
            ->assertSee('Introduction')
            ->assertSee('Greetings and Introductions')
            ->assertDontSee($inactiveCourse->name);
    }

    public function test_non_student_cannot_open_student_course_catalog(): void
    {
        Role::create(['name' => 'admin']);
        $admin = User::factory()->create();
        $admin->assignRole('admin');

        $this->actingAs($admin)
            ->get(route('student-courses.index'))
            ->assertForbidden();
    }
}
