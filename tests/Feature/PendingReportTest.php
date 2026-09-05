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

class PendingReportTest extends TestCase
{
    use RefreshDatabase;

    public function test_teacher_sees_completed_schedule_and_can_submit_one_report(): void
    {
        [$teacher, $otherTeacher, $student, $transaction, $lesson] = $this->data();
        $completed = $this->schedule($transaction, $teacher, $lesson, today()->subDay(), 1);
        $future = $this->schedule($transaction, $teacher, $lesson, today()->addDay(), 2);
        $otherSchedule = $this->schedule($transaction, $otherTeacher, $lesson, today()->subDays(2), 3);

        $this->actingAs($teacher)->get(route('pending-reports.index'))
            ->assertOk()->assertSee('Pending Reports')->assertSee('Buat Report')->assertSee($student->email)
            ->assertSee(route('pending-reports.create', $completed))
            ->assertDontSee(route('pending-reports.create', $future))
            ->assertDontSee(route('pending-reports.create', $otherSchedule));

        $this->actingAs($teacher)->get(route('pending-reports.select'))
            ->assertOk()
            ->assertSee('Buat Report Pembelajaran')
            ->assertSee('Buka Form Report')
            ->assertSee((string) $completed->id)
            ->assertDontSee('value="'.$future->id.'"', false)
            ->assertDontSee('value="'.$otherSchedule->id.'"', false);

        $this->actingAs($teacher)->get(route('pending-reports.select', ['schedule_id' => $completed->id]))
            ->assertRedirect(route('pending-reports.create', $completed));

        $this->actingAs($teacher)->get(route('pending-reports.select', ['schedule_id' => $future->id]))
            ->assertSessionHasErrors('schedule_id');

        $this->actingAs($teacher)->post(route('pending-reports.store', $completed), [
            'is_present' => '1',
            'learning_summary' => 'Student memahami materi dan menyelesaikan latihan.',
            'notes' => 'Lanjutkan latihan di rumah.',
            'video_title' => 'Dokumentasi latihan',
            'video_url' => 'https://example.com/video',
        ])->assertRedirect(route('pending-reports.index'));

        $this->assertDatabaseHas('learning_reports', [
            'paid_schedule_id' => $completed->id, 'teacher_id' => $teacher->id,
            'student_id' => $student->id, 'is_present' => true,
        ]);
        $this->actingAs($teacher)->get(route('pending-reports.index'))
            ->assertOk()->assertDontSee(route('pending-reports.create', $completed));
        $this->actingAs($teacher)->post(route('pending-reports.store', $completed), [
            'is_present' => '1', 'learning_summary' => 'Report kedua',
        ])->assertSessionHasErrors('schedule');
        $this->assertDatabaseCount('learning_reports', 1);
    }

    public function test_report_cannot_be_created_before_class_finishes_or_by_another_teacher(): void
    {
        [$teacher, $otherTeacher, , $transaction, $lesson] = $this->data();
        $future = $this->schedule($transaction, $teacher, $lesson, today()->addDay(), 1);

        $this->actingAs($teacher)->get(route('pending-reports.create', $future))->assertSessionHasErrors('schedule');
        $this->actingAs($otherTeacher)->get(route('pending-reports.create', $future))->assertNotFound();
    }

    private function data(): array
    {
        foreach (['guru', 'student'] as $role) Role::findOrCreate($role);
        $teacher = User::factory()->create(); $teacher->assignRole('guru');
        $otherTeacher = User::factory()->create(); $otherTeacher->assignRole('guru');
        $student = User::factory()->create(); $student->assignRole('student');
        $category = CourseCategory::create(['name' => 'Report', 'slug' => 'report', 'is_active' => true]);
        $course = Course::create(['course_category_id' => $category->id, 'name' => 'Report Course', 'code' => 'RPT-001', 'is_active' => true]);
        $section = CurriculumSection::create(['course_id' => $course->id, 'name' => 'Fundamental']);
        $lesson = CurriculumLesson::create(['curriculum_section_id' => $section->id, 'title' => 'Report Lesson', 'meetings' => 3]);
        $transaction = CourseTransaction::create(['course_id' => $course->id, 'user_id' => $student->id, 'amount' => 100000, 'payment_status' => 'paid']);

        return [$teacher, $otherTeacher, $student, $transaction, $lesson];
    }

    private function schedule(CourseTransaction $transaction, User $teacher, CurriculumLesson $lesson, $date, int $meeting): PaidSchedule
    {
        return PaidSchedule::create([
            'course_transaction_id' => $transaction->id, 'teacher_id' => $teacher->id,
            'curriculum_lesson_id' => $lesson->id, 'meeting_number' => $meeting,
            'training_date' => $date, 'start_time' => '09:00', 'end_time' => '10:00',
            'zoom_url' => 'https://zoom.us/j/report',
        ]);
    }
}
