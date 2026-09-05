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

class TeacherSalaryTest extends TestCase
{
    use RefreshDatabase;

    public function test_salary_is_recorded_once_after_teacher_submits_report(): void
    {
        Role::findOrCreate('guru');
        Role::findOrCreate('student');
        $teacher = User::factory()->create();
        $teacher->assignRole('guru');
        $student = User::factory()->create();
        $student->assignRole('student');
        $category = CourseCategory::create(['name' => 'Salary', 'slug' => 'salary', 'is_active' => true]);
        $course = Course::create(['course_category_id' => $category->id, 'name' => 'Salary Course', 'code' => 'SAL-COURSE', 'is_active' => true]);
        $section = CurriculumSection::create(['course_id' => $course->id, 'name' => 'Fundamental']);
        $lesson = CurriculumLesson::create(['curriculum_section_id' => $section->id, 'title' => 'Salary Lesson', 'meetings' => 2, 'fee_per_meeting' => 125000]);
        $transaction = CourseTransaction::create(['course_id' => $course->id, 'user_id' => $student->id, 'amount' => 250000, 'payment_status' => 'paid']);
        $schedule = PaidSchedule::create([
            'course_transaction_id' => $transaction->id, 'teacher_id' => $teacher->id,
            'curriculum_lesson_id' => $lesson->id, 'meeting_number' => 1,
            'training_date' => today()->subDay(), 'start_time' => '09:00', 'end_time' => '10:00',
            'zoom_url' => 'https://zoom.us/j/salary',
        ]);

        $this->actingAs($teacher)->post(route('pending-reports.store', $schedule), [
            'is_present' => '1', 'learning_summary' => 'Materi selesai dipelajari.',
        ])->assertRedirect(route('pending-reports.index'));

        $this->assertDatabaseHas('teacher_salaries', [
            'teacher_id' => $teacher->id, 'paid_schedule_id' => $schedule->id, 'amount' => 125000,
        ]);
        $this->actingAs($teacher)->get(route('teacher-salaries.index'))
            ->assertOk()->assertSee('My Salary')->assertSee('Rp 125.000')->assertSee($student->email);

        $this->actingAs($teacher)->post(route('pending-reports.store', $schedule), [
            'is_present' => '1', 'learning_summary' => 'Report kedua.',
        ])->assertSessionHasErrors('schedule');
        $this->assertDatabaseCount('teacher_salaries', 1);
    }
}
