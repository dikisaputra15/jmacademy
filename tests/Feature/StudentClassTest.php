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

class StudentClassTest extends TestCase
{
    use RefreshDatabase;

    public function test_student_only_sees_own_verified_class_and_training_schedule(): void
    {
        [$student, $otherStudent, $teacher, $course, $lesson] = $this->classData();
        $ownTransaction = $this->transaction($student, $course, 'paid');
        $otherTransaction = $this->transaction($otherStudent, $course, 'paid');
        $ownSchedule = $this->schedule($ownTransaction, $teacher, $lesson, 1, 'https://zoom.us/j/student-own');
        $this->schedule($otherTransaction, $teacher, $lesson, 2, 'https://zoom.us/j/other-student');

        $this->actingAs($student)
            ->get(route('student-classes.index'))
            ->assertOk()
            ->assertSee($course->name)
            ->assertSee($lesson->title)
            ->assertSee('Belum Aktif')
            ->assertDontSee('https://zoom.us/j/student-own')
            ->assertDontSee('https://zoom.us/j/other-student');

        $this->actingAs($student)
            ->get(route('student-classes.join', $ownSchedule))
            ->assertRedirect()
            ->assertSessionHas('error');

        $ownSchedule->update(['training_date' => today()->toDateString(), 'end_time' => '23:59:59']);

        $this->actingAs($student)
            ->get(route('student-classes.join', $ownSchedule))
            ->assertRedirect('https://zoom.us/j/student-own');

        $ownSchedule->update(['training_date' => today()->subDay()->toDateString()]);
        $this->actingAs($student)->get(route('student-classes.index'))
            ->assertOk()->assertSee('Jadwal Selesai')->assertDontSee('https://zoom.us/j/student-own');
        $this->actingAs($student)->get(route('student-classes.join', $ownSchedule))
            ->assertRedirect()->assertSessionHas('error', 'Jadwal training sudah selesai. Link Zoom tidak dapat dibuka lagi.');

        $this->actingAs($otherStudent)
            ->get(route('student-classes.join', $ownSchedule))
            ->assertNotFound();
    }

    public function test_pending_transaction_is_not_a_student_class(): void
    {
        [$student, , , $course] = $this->classData();
        $this->transaction($student, $course, 'pending');

        $this->actingAs($student)
            ->get(route('student-classes.index'))
            ->assertOk()
            ->assertSee('Belum ada class')
            ->assertDontSee('Terdaftar melalui');
    }

    public function test_verified_classes_are_displayed_as_a_paginated_table(): void
    {
        foreach (['student', 'guru'] as $role) Role::findOrCreate($role);
        $student = User::factory()->create(); $student->assignRole('student');
        $category = CourseCategory::create(['name' => 'Pagination', 'slug' => 'pagination', 'is_active' => true]);

        foreach (range(1, 11) as $number) {
            $course = Course::create([
                'course_category_id' => $category->id, 'name' => 'Course Table '.$number,
                'code' => 'TBL-'.str_pad((string) $number, 2, '0', STR_PAD_LEFT), 'is_active' => true,
            ]);
            $this->transaction($student, $course, 'paid');
        }

        $this->actingAs($student)->get(route('student-classes.index'))
            ->assertOk()->assertSee('<table', false)->assertSee('Lihat Jadwal')
            ->assertSee('Menampilkan 1–10 dari 11 class')->assertSee('page=2', false);
        $this->actingAs($student)->get(route('student-classes.index', ['page' => 2]))
            ->assertOk()->assertSee('Menampilkan 11–11 dari 11 class');
    }

    private function classData(): array
    {
        foreach (['student', 'guru'] as $role) {
            Role::findOrCreate($role);
        }
        $student = User::factory()->create(); $student->assignRole('student');
        $otherStudent = User::factory()->create(); $otherStudent->assignRole('student');
        $teacher = User::factory()->create(); $teacher->assignRole('guru');
        $category = CourseCategory::create(['name' => 'Coding', 'slug' => 'coding', 'is_active' => true]);
        $course = Course::create(['course_category_id' => $category->id, 'name' => 'Basic Python', 'code' => 'CRS-3001', 'is_active' => true]);
        $course->teachers()->attach($teacher);
        $section = CurriculumSection::create(['course_id' => $course->id, 'name' => 'Basic Python']);
        $lesson = CurriculumLesson::create(['curriculum_section_id' => $section->id, 'title' => 'Apa itu Python?', 'meetings' => 2]);

        return [$student, $otherStudent, $teacher, $course, $lesson];
    }

    private function transaction(User $student, Course $course, string $status): CourseTransaction
    {
        return CourseTransaction::create([
            'course_id' => $course->id, 'user_id' => $student->id, 'amount' => 500000,
            'sender_name' => 'Parent', 'sender_bank' => 'BCA',
            'transfer_date' => today()->toDateString(), 'payment_status' => $status,
            'verified_at' => $status === 'paid' ? now() : null,
        ]);
    }

    private function schedule(CourseTransaction $transaction, User $teacher, CurriculumLesson $lesson, int $meeting, string $zoom): PaidSchedule
    {
        return PaidSchedule::create([
            'course_transaction_id' => $transaction->id, 'teacher_id' => $teacher->id,
            'curriculum_lesson_id' => $lesson->id, 'meeting_number' => $meeting,
            'training_date' => today()->addDay()->toDateString(),
            'start_time' => '09:00', 'end_time' => '10:00', 'zoom_url' => $zoom,
        ]);
    }
}
