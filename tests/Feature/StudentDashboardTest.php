<?php

namespace Tests\Feature;

use App\Models\Course;
use App\Models\CourseCategory;
use App\Models\CourseTransaction;
use App\Models\CurriculumLesson;
use App\Models\CurriculumSection;
use App\Models\PaidSchedule;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class StudentDashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_student_overview_shows_only_four_nearest_own_schedules(): void
    {
        foreach (['student', 'guru'] as $role) Role::findOrCreate($role);
        $student = User::factory()->create(); $student->assignRole('student');
        $otherStudent = User::factory()->create(); $otherStudent->assignRole('student');
        $teacher = User::factory()->create(); $teacher->assignRole('guru');
        $category = CourseCategory::create(['name' => 'Student Overview', 'slug' => 'student-overview', 'is_active' => true]);
        $course = Course::create(['course_category_id' => $category->id, 'name' => 'Overview Course', 'code' => 'OVR-001', 'is_active' => true]);
        $course->teachers()->attach($teacher);
        $section = CurriculumSection::create(['course_id' => $course->id, 'name' => 'Overview']);
        $transaction = CourseTransaction::create(['course_id' => $course->id, 'user_id' => $student->id, 'amount' => 100000, 'payment_status' => 'paid']);
        $otherTransaction = CourseTransaction::create(['course_id' => $course->id, 'user_id' => $otherStudent->id, 'amount' => 100000, 'payment_status' => 'paid']);

        foreach (range(1, 5) as $number) {
            $lesson = CurriculumLesson::create(['curriculum_section_id' => $section->id, 'title' => "Jadwal Student {$number}", 'meetings' => 1, 'sort_order' => $number]);
            PaidSchedule::create(['course_transaction_id' => $transaction->id, 'teacher_id' => $teacher->id, 'curriculum_lesson_id' => $lesson->id, 'meeting_number' => 1, 'training_date' => today()->addDays($number), 'start_time' => '09:00', 'end_time' => '10:00', 'zoom_url' => 'https://zoom.us/j/student-dashboard']);
        }
        $otherLesson = CurriculumLesson::create(['curriculum_section_id' => $section->id, 'title' => 'Jadwal Student Lain', 'meetings' => 1, 'sort_order' => 10]);
        PaidSchedule::create(['course_transaction_id' => $otherTransaction->id, 'teacher_id' => $teacher->id, 'curriculum_lesson_id' => $otherLesson->id, 'meeting_number' => 1, 'training_date' => today()->addDay(), 'start_time' => '11:00', 'end_time' => '12:00', 'zoom_url' => 'https://zoom.us/j/other-dashboard']);

        $this->actingAs($student)->get(route('home'))
            ->assertOk()->assertSee('Overview Student')->assertSee('4 Jadwal Training Terdekat')
            ->assertSee('Jadwal Student 1')->assertSee('Jadwal Student 4')
            ->assertDontSee('Jadwal Student 5')->assertDontSee('Jadwal Student Lain')
            ->assertSee('Zoom Belum Aktif');
    }
}
