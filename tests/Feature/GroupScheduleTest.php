<?php

namespace Tests\Feature;

use App\Models\{ClassCategory, Course, CourseCategory, CourseTransaction, User};
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class GroupScheduleTest extends TestCase
{
    use RefreshDatabase;

    private function setupGroup(): array
    {
        foreach (['admin', 'guru', 'student'] as $role) {
            Role::findOrCreate($role);
        }
        $admin = User::factory()->create();
        $admin->assignRole('admin');
        $teacher = User::factory()->create(['is_active' => true]);
        $teacher->assignRole('guru');
        $category = ClassCategory::where('name', 'Reguler A')->firstOrFail();
        $courseCategory = CourseCategory::create(['name' => 'Coding', 'slug' => 'coding', 'is_active' => true]);
        $course = Course::create(['course_category_id' => $courseCategory->id, 'name' => 'Coding A', 'code' => 'GROUP', 'is_active' => true]);
        $section = $course->sections()->create(['name' => 'Dasar']);
        $lesson = $section->lessons()->create(['title' => 'Coding', 'meetings' => 2]);
        $ids = [];
        for ($i = 0; $i < 3; $i++) {
            $student = User::factory()->create();
            $student->assignRole('student');
            $ids[] = CourseTransaction::create(['course_id' => $course->id, 'class_category_id' => $category->id,
                'user_id' => $student->id, 'payment_status' => 'paid', 'amount' => 50000])->id;
        }
        $slots = [];
        for ($i = 1; $i <= 2; $i++) {
            $slots[] = ['lesson_id' => $lesson->id, 'meeting_number' => $i, 'training_date' => today()->addDays($i)->toDateString(),
                'start_time' => '10:00', 'end_time' => '11:00', 'zoom_url' => 'https://zoom.us/j/123'];
        }
        $this->actingAs($admin);
        return ['transaction_ids' => $ids, 'teacher_id' => $teacher->id, 'schedules' => $slots];
    }

    public function test_full_group_receives_same_teacher_and_all_schedules(): void
    {
        $data = $this->setupGroup();
        $this->get(route('student-registrations.index'))->assertOk()->assertSee('3/3 siswa')->assertSee('Siap dijadwalkan');
        $this->post(route('student-registrations.group-schedules'), $data)->assertSessionHasNoErrors();
        $this->assertDatabaseCount('paid_schedules', 6);
        foreach ($data['transaction_ids'] as $id) {
            $this->assertDatabaseHas('paid_schedules', ['course_transaction_id' => $id, 'teacher_id' => $data['teacher_id'], 'meeting_number' => 2]);
        }
        $this->post(route('student-registrations.group-schedules'), $data)->assertSessionHasErrors('group');
        $this->assertDatabaseCount('paid_schedules', 6);
    }

    public function test_incomplete_or_unpaid_group_is_rejected(): void
    {
        $data = $this->setupGroup();
        $this->post(route('student-registrations.group-schedules'), [...$data, 'transaction_ids' => array_slice($data['transaction_ids'], 0, 2)])->assertSessionHasErrors('group');
        CourseTransaction::findOrFail($data['transaction_ids'][0])->update(['payment_status' => 'pending']);
        $this->post(route('student-registrations.group-schedules'), $data)->assertSessionHasErrors('group');
        $this->assertDatabaseCount('paid_schedules', 0);
        $this->get(route('student-registrations.index'))->assertOk()->assertSee('2/3 siswa')->assertSee('Membutuhkan 1 siswa lagi');
    }

    public function test_conflicting_slots_roll_back_entire_group(): void
    {
        $data = $this->setupGroup();
        $data['schedules'][1]['training_date'] = $data['schedules'][0]['training_date'];
        $this->post(route('student-registrations.group-schedules'), $data)->assertSessionHasErrors('schedules.1.training_date');
        $this->assertDatabaseCount('paid_schedules', 0);
    }

    public function test_students_cannot_assign_group_schedules(): void
    {
        $data = $this->setupGroup();
        $student = CourseTransaction::findOrFail($data['transaction_ids'][0])->student;
        $this->actingAs($student)->post(route('student-registrations.group-schedules'), $data)->assertForbidden();
        $this->assertDatabaseCount('paid_schedules', 0);
    }
}
