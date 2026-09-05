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

class PaidScheduleTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_schedule_paid_student_with_assigned_teacher(): void
    {
        [$admin, , $teacher, $transaction, $lesson] = $this->scheduleData('paid');

        $this->actingAs($admin)
            ->get(route('paid-schedules.index'))
            ->assertOk()
            ->assertSee($teacher->name)
            ->assertSee($lesson->title);

        $this->actingAs($admin)->post(route('paid-schedules.store'), [
            'course_transaction_id' => $transaction->id,
            'notes' => 'Training online',
            'schedules' => [
                ['lesson_id' => $lesson->id, 'meeting_number' => 1, 'training_date' => now()->addDay()->toDateString(), 'start_time' => '09:00', 'end_time' => '10:00', 'zoom_url' => 'https://zoom.us/j/123456'],
                ['lesson_id' => $lesson->id, 'meeting_number' => 2, 'training_date' => now()->addDays(2)->toDateString(), 'start_time' => '09:00', 'end_time' => '10:00', 'zoom_url' => 'https://zoom.us/j/123456'],
            ],
        ])->assertRedirect(route('paid-schedules.index'))
            ->assertSessionHas('success');

        $this->assertDatabaseCount('paid_schedules', 2);
        $this->assertDatabaseHas('paid_schedules', [
            'course_transaction_id' => $transaction->id,
            'teacher_id' => $teacher->id,
            'curriculum_lesson_id' => $lesson->id,
            'meeting_number' => 1,
            'zoom_url' => 'https://zoom.us/j/123456',
            'created_by' => $admin->id,
        ]);
    }

    public function test_pending_payment_cannot_be_scheduled(): void
    {
        [$admin, , , $transaction, $lesson] = $this->scheduleData('pending');

        $this->actingAs($admin)->post(route('paid-schedules.store'), [
            'course_transaction_id' => $transaction->id,
            'schedules' => [[
                'lesson_id' => $lesson->id,
                'meeting_number' => 1,
                'training_date' => now()->addDay()->toDateString(),
                'start_time' => '09:00',
                'end_time' => '10:00',
                'zoom_url' => 'https://zoom.us/j/123456',
            ]],
        ])->assertNotFound();

        $this->assertDatabaseCount('paid_schedules', 0);
    }

    public function test_course_without_assigned_teacher_cannot_be_scheduled(): void
    {
        [$admin, , $teacher, $transaction, $lesson] = $this->scheduleData('paid');
        $transaction->course->teachers()->detach($teacher);

        $this->actingAs($admin)->post(route('paid-schedules.store'), [
            'course_transaction_id' => $transaction->id,
            'schedules' => [[
                'lesson_id' => $lesson->id,
                'meeting_number' => 1,
                'training_date' => now()->addDay()->toDateString(),
                'start_time' => '09:00', 'end_time' => '10:00',
                'zoom_url' => 'https://zoom.us/j/123456',
            ]],
        ])->assertSessionHasErrors('course_transaction_id');
    }

    public function test_teacher_only_sees_paid_schedules_assigned_to_them(): void
    {
        [$admin, , $teacher, $transaction, $lesson] = $this->scheduleData('paid');
        $otherTeacher = User::factory()->create();
        $otherTeacher->assignRole('guru');

        $teacherSchedule = PaidSchedule::create([
            'course_transaction_id' => $transaction->id,
            'teacher_id' => $teacher->id,
            'curriculum_lesson_id' => $lesson->id,
            'meeting_number' => 1,
            'training_date' => now()->addDay()->toDateString(),
            'start_time' => '09:00', 'end_time' => '10:00',
            'zoom_url' => 'https://zoom.us/j/for-this-teacher',
            'created_by' => $admin->id,
        ]);
        PaidSchedule::create([
            'course_transaction_id' => $transaction->id,
            'teacher_id' => $otherTeacher->id,
            'curriculum_lesson_id' => $lesson->id,
            'meeting_number' => 2,
            'training_date' => now()->addDays(2)->toDateString(),
            'start_time' => '09:00', 'end_time' => '10:00',
            'zoom_url' => 'https://zoom.us/j/other-teacher',
            'created_by' => $admin->id,
        ]);

        $this->actingAs($teacher)
            ->get(route('teacher-paid-schedules.index'))
            ->assertOk()
            ->assertSee('Daftar Paid Schedules')
            ->assertSee('Zoom Belum Aktif')
            ->assertDontSee('https://zoom.us/j/for-this-teacher')
            ->assertDontSee('https://zoom.us/j/other-teacher');

        $this->actingAs($teacher)
            ->get(route('teacher-paid-schedules.join', $teacherSchedule))
            ->assertRedirect()
            ->assertSessionHas('error');

        $teacherSchedule->update(['training_date' => today()->toDateString(), 'end_time' => '23:59:59']);

        $this->actingAs($teacher)
            ->get(route('teacher-paid-schedules.join', $teacherSchedule))
            ->assertRedirect('https://zoom.us/j/for-this-teacher');

        $teacherSchedule->update(['training_date' => today()->subDay()->toDateString()]);
        $this->actingAs($teacher)->get(route('teacher-paid-schedules.index', ['filter' => 'past']))
            ->assertOk()->assertSee('Jadwal Selesai')->assertDontSee('https://zoom.us/j/for-this-teacher');
        $this->actingAs($teacher)->get(route('teacher-paid-schedules.join', $teacherSchedule))
            ->assertRedirect()->assertSessionHas('error', 'Jadwal training sudah selesai. Link Zoom tidak dapat dibuka lagi.');

        $this->actingAs($admin)
            ->get(route('teacher-paid-schedules.index'))
            ->assertForbidden();
    }

    private function scheduleData(string $status): array
    {
        foreach (['admin', 'student', 'guru'] as $role) {
            Role::findOrCreate($role);
        }

        $admin = User::factory()->create(); $admin->assignRole('admin');
        $student = User::factory()->create(); $student->assignRole('student');
        $teacher = User::factory()->create(); $teacher->assignRole('guru');
        $category = CourseCategory::create(['name' => 'English', 'slug' => 'english', 'is_active' => true]);
        $course = Course::create([
            'course_category_id' => $category->id, 'name' => 'English Starter',
            'code' => 'CRS-2001', 'is_active' => true,
        ]);
        $course->teachers()->attach($teacher);
        $section = CurriculumSection::create([
            'course_id' => $course->id,
            'name' => 'Fundamental',
        ]);
        $lesson = CurriculumLesson::create([
            'curriculum_section_id' => $section->id,
            'title' => 'Introduction',
            'meetings' => 2,
        ]);
        $transaction = CourseTransaction::create([
            'course_id' => $course->id, 'user_id' => $student->id, 'amount' => 500000,
            'sender_name' => 'Parent', 'sender_bank' => 'BCA',
            'transfer_date' => now()->toDateString(), 'payment_status' => $status,
        ]);

        return [$admin, $student, $teacher, $transaction, $lesson];
    }
}
