<?php

namespace Tests\Feature;

use App\Models\Course;
use App\Models\CourseCategory;
use App\Models\CourseTransaction;
use App\Models\PaidSchedule;
use App\Models\TrialSchedule;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class AdminDashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_overview_shows_statistics_and_teacher_calendar(): void
    {
        foreach (['admin', 'guru', 'student'] as $role) Role::findOrCreate($role);
        $admin = User::factory()->create(); $admin->assignRole('admin');
        $teacher = User::factory()->create(['name' => 'Guru Kalender']); $teacher->assignRole('guru');
        $secondTeacher = User::factory()->create(); $secondTeacher->assignRole('guru');
        $student = User::factory()->create(['name' => 'Siswa Kalender']); $student->assignRole('student');
        $category = CourseCategory::create(['name' => 'Dashboard Admin', 'slug' => 'dashboard-admin', 'is_active' => true]);
        $course = Course::create(['course_category_id' => $category->id, 'name' => 'Course Kalender', 'code' => 'CAL-001', 'is_active' => true]);
        Course::create(['course_category_id' => $category->id, 'name' => 'Course Nonaktif', 'code' => 'CAL-002', 'is_active' => false]);
        $course->teachers()->attach($teacher);
        $paidTransaction = CourseTransaction::create(['course_id' => $course->id, 'user_id' => $student->id, 'amount' => 100000, 'payment_status' => 'paid']);
        CourseTransaction::create(['course_id' => $course->id, 'user_id' => $student->id, 'amount' => 100000, 'payment_status' => 'pending']);
        PaidSchedule::create(['course_transaction_id' => $paidTransaction->id, 'teacher_id' => $teacher->id, 'training_date' => today(), 'start_time' => '09:00', 'end_time' => '10:00', 'zoom_url' => 'https://zoom.us/j/admin-paid', 'created_by' => $admin->id]);
        TrialSchedule::create(['student_id' => $student->id, 'course_id' => $course->id, 'teacher_id' => $teacher->id, 'training_date' => today(), 'start_time' => '11:00', 'end_time' => '12:00', 'zoom_url' => 'https://zoom.us/j/admin-trial', 'created_by' => $admin->id]);

        $this->actingAs($admin)->get(route('home'))
            ->assertOk()->assertSee('Overview Admin')->assertSee('Jumlah Guru')
            ->assertSee('Jumlah Siswa')->assertSee('Course Aktif')->assertSee('Pembayaran Berhasil')
            ->assertSee('Kalender Jadwal Guru')->assertSee('Guru Kalender')->assertSee('Course Kalender')
            ->assertViewHas('teacherCount', 2)->assertViewHas('adminStudentCount', 1)
            ->assertViewHas('activeCourseCount', 1)->assertViewHas('successfulTransactionCount', 1);
    }
}
