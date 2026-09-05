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

class TeacherHistoryTest extends TestCase
{
    use RefreshDatabase;

    public function test_submitted_learning_report_appears_only_in_its_teachers_history(): void
    {
        Role::findOrCreate('guru'); Role::findOrCreate('student');
        $teacher = User::factory()->create(); $teacher->assignRole('guru');
        $otherTeacher = User::factory()->create(); $otherTeacher->assignRole('guru');
        $student = User::factory()->create(); $student->assignRole('student');
        $category = CourseCategory::create(['name' => 'History', 'slug' => 'history', 'is_active' => true]);
        $course = Course::create(['course_category_id' => $category->id, 'name' => 'History Course', 'code' => 'HST-001', 'is_active' => true]);
        $section = CurriculumSection::create(['course_id' => $course->id, 'name' => 'Fundamental']);
        $lesson = CurriculumLesson::create(['curriculum_section_id' => $section->id, 'title' => 'History Lesson', 'meetings' => 1, 'fee_per_meeting' => 90000]);
        $transaction = CourseTransaction::create(['course_id' => $course->id, 'user_id' => $student->id, 'amount' => 90000, 'payment_status' => 'paid']);
        $schedule = PaidSchedule::create([
            'course_transaction_id' => $transaction->id, 'teacher_id' => $teacher->id,
            'curriculum_lesson_id' => $lesson->id, 'meeting_number' => 1,
            'training_date' => today()->subDay(), 'start_time' => '09:00', 'end_time' => '10:00',
            'zoom_url' => 'https://zoom.us/j/history',
        ]);

        $this->actingAs($teacher)->get(route('teacher-histories.index'))
            ->assertOk()->assertSee('Belum ada history report');
        $this->actingAs($teacher)->post(route('pending-reports.store', $schedule), [
            'is_present' => '1',
            'learning_summary' => 'Student berhasil memahami history lesson.',
            'notes' => 'Latihan mandiri di rumah.',
            'video_title' => 'Rekaman History',
            'video_url' => 'https://example.com/history-video',
        ])->assertRedirect(route('pending-reports.index'));

        $this->actingAs($teacher)->get(route('teacher-histories.index'))
            ->assertOk()->assertSee('Histories')->assertSee($student->email)
            ->assertSee('History Lesson')->assertSee('Student berhasil memahami history lesson.')
            ->assertSee('Rekaman History')->assertSee('Rp 90.000');
        $this->actingAs($otherTeacher)->get(route('teacher-histories.index'))
            ->assertOk()->assertDontSee($student->email);
        $this->actingAs($teacher)->get(route('teacher-histories.index', ['search' => 'tidak-ada']))
            ->assertOk()->assertSee('Belum ada history report');
    }
}
