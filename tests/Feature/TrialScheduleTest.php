<?php

namespace Tests\Feature;

use App\Models\Course;
use App\Models\CourseCategory;
use App\Models\CurriculumLesson;
use App\Models\CurriculumSection;
use App\Models\TrialSchedule;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class TrialScheduleTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_create_only_one_trial_for_each_student(): void
    {
        [$admin, $student, $teacher, $course, $lesson] = $this->data();
        $payload = [
            'student_id' => $student->id, 'course_id' => $course->id,
            'curriculum_lesson_id' => $lesson->id,
            'training_date' => today()->addDay()->toDateString(),
            'start_time' => '09:00', 'end_time' => '10:00',
            'zoom_url' => 'https://zoom.us/j/trial', 'notes' => 'Trial pertama',
        ];

        $this->actingAs($admin)->post(route('trial-schedules.store'), $payload)
            ->assertRedirect(route('trial-schedules.index'));

        $this->assertDatabaseHas('trial_schedules', [
            'student_id' => $student->id, 'teacher_id' => $teacher->id, 'course_id' => $course->id,
        ]);

        $this->actingAs($admin)->post(route('trial-schedules.store'), [
            ...$payload, 'training_date' => today()->addDays(2)->toDateString(),
        ])->assertSessionHasErrors('student_id');
        $this->assertDatabaseCount('trial_schedules', 1);
    }

    public function test_trial_page_hides_students_who_already_used_trial_and_rejects_teacher_access(): void
    {
        [$admin, $student, $teacher, $course] = $this->data();
        TrialSchedule::create([
            'student_id' => $student->id, 'course_id' => $course->id, 'teacher_id' => $teacher->id,
            'training_date' => today()->addDay(), 'start_time' => '10:00', 'end_time' => '11:00',
            'zoom_url' => 'https://zoom.us/j/trial', 'created_by' => $admin->id,
        ]);

        $this->actingAs($admin)->get(route('trial-schedules.index'))
            ->assertOk()->assertSee('Trial Schedules')->assertSee($student->email)
            ->assertSee('Tidak ada student yang tersedia');
        $this->actingAs($teacher)->get(route('trial-schedules.index'))->assertForbidden();
    }

    public function test_teacher_only_sees_their_trial_and_zoom_opens_on_trial_date(): void
    {
        [$admin, $student, $teacher, $course, $lesson] = $this->data();
        $otherTeacher = User::factory()->create(); $otherTeacher->assignRole('guru');
        $otherStudent = User::factory()->create(); $otherStudent->assignRole('student');
        $ownSchedule = TrialSchedule::create([
            'student_id' => $student->id, 'course_id' => $course->id, 'teacher_id' => $teacher->id,
            'curriculum_lesson_id' => $lesson->id, 'training_date' => today()->addDay(),
            'start_time' => '09:00', 'end_time' => '10:00', 'zoom_url' => 'https://zoom.us/j/own-trial', 'created_by' => $admin->id,
        ]);
        TrialSchedule::create([
            'student_id' => $otherStudent->id, 'course_id' => $course->id, 'teacher_id' => $otherTeacher->id,
            'training_date' => today()->addDay(), 'start_time' => '11:00', 'end_time' => '12:00',
            'zoom_url' => 'https://zoom.us/j/other-trial', 'created_by' => $admin->id,
        ]);

        $this->actingAs($teacher)->get(route('teacher-trial-schedules.index'))
            ->assertOk()->assertSee('Daftar Trial Schedules')
            ->assertSee($student->email)->assertDontSee($otherStudent->email)
            ->assertSee('Zoom Belum Aktif')->assertDontSee('https://zoom.us/j/own-trial');
        $this->actingAs($teacher)->get(route('teacher-trial-schedules.join', $ownSchedule))
            ->assertRedirect()->assertSessionHas('error');

        $ownSchedule->update(['training_date' => today(), 'end_time' => '23:59:59']);
        $this->actingAs($teacher)->get(route('teacher-trial-schedules.join', $ownSchedule))
            ->assertRedirect('https://zoom.us/j/own-trial');
        $ownSchedule->update(['training_date' => today()->subDay()]);
        $this->actingAs($teacher)->get(route('teacher-trial-schedules.index', ['filter' => 'past']))
            ->assertOk()->assertSee('Jadwal Selesai')->assertDontSee('https://zoom.us/j/own-trial');
        $this->actingAs($teacher)->get(route('teacher-trial-schedules.join', $ownSchedule))
            ->assertRedirect()->assertSessionHas('error', 'Jadwal trial sudah selesai. Link Zoom tidak dapat dibuka lagi.');
        $this->actingAs($admin)->get(route('teacher-trial-schedules.index'))->assertForbidden();
    }

    private function data(): array
    {
        foreach (['admin', 'student', 'guru'] as $role) Role::findOrCreate($role);
        $admin = User::factory()->create(); $admin->assignRole('admin');
        $student = User::factory()->create(); $student->assignRole('student');
        $teacher = User::factory()->create(); $teacher->assignRole('guru');
        $category = CourseCategory::create(['name' => 'Trial', 'slug' => 'trial', 'is_active' => true]);
        $course = Course::create(['course_category_id' => $category->id, 'name' => 'Trial Course', 'code' => 'TRY-001', 'is_active' => true]);
        $course->teachers()->attach($teacher);
        $section = CurriculumSection::create(['course_id' => $course->id, 'name' => 'Pengenalan']);
        $lesson = CurriculumLesson::create(['curriculum_section_id' => $section->id, 'title' => 'Materi Trial', 'meetings' => 1]);

        return [$admin, $student, $teacher, $course, $lesson];
    }
}
