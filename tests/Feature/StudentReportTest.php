<?php

namespace Tests\Feature;

use App\Models\Course;
use App\Models\CourseCategory;
use App\Models\CourseTransaction;
use App\Models\CurriculumLesson;
use App\Models\CurriculumSection;
use App\Models\LearningReport;
use App\Models\PaidSchedule;
use App\Models\ParentReport;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class StudentReportTest extends TestCase
{
    use RefreshDatabase;

    public function test_student_can_view_own_report_and_certificate_but_not_another_students(): void
    {
        Role::findOrCreate('guru'); Role::findOrCreate('student');
        $teacher = User::factory()->create(['name' => 'Guru Report']); $teacher->assignRole('guru');
        $student = User::factory()->create(['name' => 'Student Report']); $student->assignRole('student');
        $otherStudent = User::factory()->create(); $otherStudent->assignRole('student');
        $category = CourseCategory::create(['name' => 'Coding', 'slug' => 'coding-report', 'is_active' => true]);
        $course = Course::create(['course_category_id' => $category->id, 'name' => 'Python Complete', 'code' => 'PY-CERT', 'is_active' => true]);
        $section = CurriculumSection::create(['course_id' => $course->id, 'name' => 'Fundamental']);
        $lesson = CurriculumLesson::create(['curriculum_section_id' => $section->id, 'title' => 'Final Project', 'meetings' => 1, 'fee_per_meeting' => 100000]);
        $transaction = CourseTransaction::create(['course_id' => $course->id, 'user_id' => $student->id, 'amount' => 100000, 'payment_status' => 'paid']);
        $schedule = PaidSchedule::create(['course_transaction_id' => $transaction->id, 'teacher_id' => $teacher->id, 'curriculum_lesson_id' => $lesson->id, 'meeting_number' => 1, 'training_date' => today()->subDay(), 'start_time' => '09:00', 'end_time' => '10:00', 'zoom_url' => 'https://zoom.us/j/report-study']);
        LearningReport::create(['paid_schedule_id' => $schedule->id, 'teacher_id' => $teacher->id, 'student_id' => $student->id, 'course_id' => $course->id, 'curriculum_lesson_id' => $lesson->id, 'is_present' => true, 'learning_summary' => 'Final project selesai dengan baik.', 'submitted_at' => now()]);
        $report = ParentReport::create(['course_transaction_id' => $transaction->id, 'teacher_id' => $teacher->id, 'student_id' => $student->id, 'course_id' => $course->id, 'grade' => 'A', 'understanding' => true, 'logic' => true, 'creativity' => true, 'strengths' => 'Sangat baik dalam menyelesaikan project.', 'recommendation' => 'Lanjut ke tingkat berikutnya.', 'submitted_at' => now()]);

        $this->actingAs($student)->get(route('student-reports.index'))
            ->assertOk()->assertSee('Report Study')->assertSee('Python Complete')
            ->assertSee('Lihat Sertifikat')->assertSee(route('student-reports.certificate', $report));
        $this->actingAs($student)->get(route('student-reports.show', $report))
            ->assertOk()->assertSee('Final Project')->assertSee('Final project selesai dengan baik.')
            ->assertSee('Sangat baik dalam menyelesaikan project.');
        $this->actingAs($student)->get(route('student-reports.certificate', $report))
            ->assertOk()->assertSee('Sertifikat')->assertSee('Student Report')
            ->assertSee('Python Complete')->assertSee('CERT-'.now()->format('Y'));

        $this->actingAs($otherStudent)->get(route('student-reports.show', $report))->assertNotFound();
        $this->actingAs($otherStudent)->get(route('student-reports.certificate', $report))->assertNotFound();
        $this->actingAs($otherStudent)->get(route('student-reports.index'))->assertDontSee('Python Complete');
    }
}
