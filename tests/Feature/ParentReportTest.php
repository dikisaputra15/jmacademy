<?php

namespace Tests\Feature;

use App\Models\Course;
use App\Models\CourseCategory;
use App\Models\CourseTransaction;
use App\Models\CurriculumLesson;
use App\Models\CurriculumSection;
use App\Models\LearningReport;
use App\Models\PaidSchedule;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class ParentReportTest extends TestCase
{
    use RefreshDatabase;

    public function test_parent_report_is_available_only_after_all_meeting_reports_are_complete(): void
    {
        [$teacher, $otherTeacher, $student, $transaction, $lesson] = $this->data();
        $first = $this->schedule($transaction, $teacher, $lesson, today()->subDays(2), 1);
        $second = $this->schedule($transaction, $teacher, $lesson, today()->subDay(), 2);
        $this->report($first, $teacher, $student, $transaction, $lesson);

        $this->actingAs($teacher)->get(route('parent-reports.index'))
            ->assertOk()->assertSee('Parent Reports')->assertDontSee($student->email);
        $this->actingAs($teacher)->get(route('parent-reports.create', $transaction))->assertNotFound();

        $this->report($second, $teacher, $student, $transaction, $lesson);

        $this->actingAs($teacher)->get(route('parent-reports.index'))
            ->assertOk()->assertSee($student->email)->assertSee('Bisa Buat Laporan')
            ->assertSee(route('parent-reports.create', $transaction));
        $this->actingAs($teacher)->get(route('parent-reports.create', $transaction))
            ->assertOk()->assertSee('Laporan Pembelajaran')->assertSee('Pertemuan 1')
            ->assertSee('Pertemuan 2')->assertSee('Penilaian Akhir');
        $this->actingAs($otherTeacher)->get(route('parent-reports.create', $transaction))->assertNotFound();

        $this->actingAs($teacher)->post(route('parent-reports.store', $transaction), [
            'grade' => 'A', 'understanding' => '1', 'logic' => '1', 'creativity' => '1',
            'strengths' => 'Student memahami seluruh materi dengan sangat baik.',
            'improvements' => 'Perlu lebih sering berlatih mandiri.',
            'recommendation' => 'Lanjutkan ke course tingkat berikutnya.',
        ])->assertRedirect(route('parent-reports.index'));

        $this->assertDatabaseHas('parent_reports', [
            'course_transaction_id' => $transaction->id, 'teacher_id' => $teacher->id,
            'student_id' => $student->id, 'grade' => 'A', 'understanding' => true,
        ]);
        $this->actingAs($teacher)->post(route('parent-reports.store', $transaction), [
            'grade' => 'B', 'strengths' => 'Report kedua.',
        ])->assertSessionHasErrors('report');
        $this->assertDatabaseCount('parent_reports', 1);
    }

    private function data(): array
    {
        Role::findOrCreate('guru'); Role::findOrCreate('student');
        $teacher = User::factory()->create(); $teacher->assignRole('guru');
        $otherTeacher = User::factory()->create(); $otherTeacher->assignRole('guru');
        $student = User::factory()->create(); $student->assignRole('student');
        $category = CourseCategory::create(['name' => 'Parent', 'slug' => 'parent', 'is_active' => true]);
        $course = Course::create(['course_category_id' => $category->id, 'name' => 'Parent Course', 'code' => 'PRT-001', 'is_active' => true]);
        $section = CurriculumSection::create(['course_id' => $course->id, 'name' => 'Fundamental']);
        $lesson = CurriculumLesson::create(['curriculum_section_id' => $section->id, 'title' => 'Parent Lesson', 'meetings' => 2, 'fee_per_meeting' => 100000]);
        $transaction = CourseTransaction::create(['course_id' => $course->id, 'user_id' => $student->id, 'amount' => 200000, 'payment_status' => 'paid']);
        return [$teacher, $otherTeacher, $student, $transaction, $lesson];
    }

    private function schedule($transaction, $teacher, $lesson, $date, int $meeting): PaidSchedule
    {
        return PaidSchedule::create([
            'course_transaction_id' => $transaction->id, 'teacher_id' => $teacher->id,
            'curriculum_lesson_id' => $lesson->id, 'meeting_number' => $meeting,
            'training_date' => $date, 'start_time' => '09:00', 'end_time' => '10:00',
            'zoom_url' => 'https://zoom.us/j/parent',
        ]);
    }

    private function report($schedule, $teacher, $student, $transaction, $lesson): void
    {
        LearningReport::create([
            'paid_schedule_id' => $schedule->id, 'teacher_id' => $teacher->id,
            'student_id' => $student->id, 'course_id' => $transaction->course_id,
            'curriculum_lesson_id' => $lesson->id, 'is_present' => true,
            'learning_summary' => 'Materi pertemuan telah dipahami.', 'submitted_at' => now(),
        ]);
    }
}
