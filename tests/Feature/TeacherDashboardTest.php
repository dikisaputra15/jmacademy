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

class TeacherDashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_teacher_overview_shows_student_count_and_six_nearest_schedules(): void
    {
        foreach (['guru', 'student'] as $role) Role::findOrCreate($role);
        $teacher = User::factory()->create(); $teacher->assignRole('guru');
        $student = User::factory()->create(); $student->assignRole('student');
        $category = CourseCategory::create(['name' => 'Programming', 'slug' => 'programming', 'is_active' => true]);
        $course = Course::create(['course_category_id' => $category->id, 'name' => 'Python Dashboard', 'code' => 'PY-DASH', 'is_active' => true]);
        $course->teachers()->attach($teacher);
        $section = CurriculumSection::create(['course_id' => $course->id, 'name' => 'Fundamental']);
        $transaction = CourseTransaction::create(['course_id' => $course->id, 'user_id' => $student->id, 'amount' => 100000, 'payment_status' => 'paid']);

        foreach (range(1, 7) as $number) {
            $lesson = CurriculumLesson::create(['curriculum_section_id' => $section->id, 'title' => "Materi Dashboard {$number}", 'meetings' => 1, 'sort_order' => $number]);
            PaidSchedule::create([
                'course_transaction_id' => $transaction->id, 'teacher_id' => $teacher->id,
                'curriculum_lesson_id' => $lesson->id, 'meeting_number' => 1,
                'training_date' => today()->addDays($number), 'start_time' => '09:00', 'end_time' => '10:00',
                'zoom_url' => 'https://zoom.us/j/dashboard', 'created_by' => $teacher->id,
            ]);
        }

        $this->actingAs($teacher)
            ->get(route('home'))
            ->assertOk()
            ->assertSee('Overview Guru')
            ->assertSee('Siswa yang Diajar')
            ->assertSee('Materi Dashboard 1')
            ->assertSee('Materi Dashboard 6')
            ->assertDontSee('Materi Dashboard 7')
            ->assertSee('Zoom Belum Aktif');
    }
}
