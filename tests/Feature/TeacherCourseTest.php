<?php

namespace Tests\Feature;

use App\Models\Course;
use App\Models\CourseCategory;
use App\Models\CourseTransaction;
use App\Models\CurriculumLesson;
use App\Models\CurriculumSection;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class TeacherCourseTest extends TestCase
{
    use RefreshDatabase;

    public function test_teacher_only_sees_assigned_courses_with_curriculum_and_verified_students(): void
    {
        foreach (['admin', 'guru', 'student'] as $role) {
            Role::findOrCreate($role);
        }
        $teacher = User::factory()->create(); $teacher->assignRole('guru');
        $otherTeacher = User::factory()->create(); $otherTeacher->assignRole('guru');
        $student = User::factory()->create(['name' => 'Student Verified']); $student->assignRole('student');
        $category = CourseCategory::create(['name' => 'Programming', 'slug' => 'programming', 'is_active' => true]);
        $assigned = Course::create(['course_category_id' => $category->id, 'name' => 'Basic Python', 'code' => 'CRS-6001', 'is_active' => true]);
        $notAssigned = Course::create(['course_category_id' => $category->id, 'name' => 'Secret Robotics', 'code' => 'CRS-6002', 'is_active' => true]);
        $assigned->teachers()->attach($teacher);
        $notAssigned->teachers()->attach($otherTeacher);
        $section = CurriculumSection::create(['course_id' => $assigned->id, 'name' => 'Fundamental']);
        CurriculumLesson::create(['curriculum_section_id' => $section->id, 'title' => 'Python Introduction', 'meetings' => 2]);
        CourseTransaction::create([
            'course_id' => $assigned->id, 'user_id' => $student->id,
            'amount' => 500000, 'payment_status' => 'paid',
        ]);

        $this->actingAs($teacher)
            ->get(route('teacher-courses.index'))
            ->assertOk()
            ->assertSee('Basic Python')
            ->assertSee('Fundamental')
            ->assertSee('Python Introduction')
            ->assertSee('Student Verified')
            ->assertDontSee('Secret Robotics');
    }

    public function test_non_teacher_cannot_open_teacher_courses(): void
    {
        Role::findOrCreate('admin');
        $admin = User::factory()->create();
        $admin->assignRole('admin');

        $this->actingAs($admin)
            ->get(route('teacher-courses.index'))
            ->assertForbidden();
    }
}
