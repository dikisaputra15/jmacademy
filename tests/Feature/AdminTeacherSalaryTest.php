<?php

namespace Tests\Feature;

use App\Models\Course;
use App\Models\CourseCategory;
use App\Models\CourseTransaction;
use App\Models\CurriculumLesson;
use App\Models\CurriculumSection;
use App\Models\PaidSchedule;
use App\Models\TeacherPayout;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class AdminTeacherSalaryTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_pays_selected_month_with_transfer_proof_and_report(): void
    {
        $this->travelTo(\Carbon\Carbon::parse('2026-09-16 12:00:00'));
        Storage::fake();
        foreach (['admin', 'guru', 'student'] as $role) Role::findOrCreate($role);
        $admin = User::factory()->create(); $admin->assignRole('admin');
        $teacher = User::factory()->create([
            'name' => 'Guru Gaji', 'bank_name' => 'BCA',
            'bank_account_number' => '1234567890', 'bank_account_holder' => 'Guru Gaji',
        ]); $teacher->assignRole('guru');
        $student = User::factory()->create(); $student->assignRole('student');
        $category = CourseCategory::create(['name' => 'Gaji', 'slug' => 'gaji', 'is_active' => true]);
        $course = Course::create(['course_category_id' => $category->id, 'name' => 'Course Gaji', 'code' => 'GAJI-01', 'is_active' => true]);
        $section = CurriculumSection::create(['course_id' => $course->id, 'name' => 'Dasar']);
        $lesson = CurriculumLesson::create(['curriculum_section_id' => $section->id, 'title' => 'Materi Gaji', 'meetings' => 2, 'fee_per_meeting' => 125000]);
        $transaction = CourseTransaction::create(['course_id' => $course->id, 'user_id' => $student->id, 'amount' => 250000, 'payment_status' => 'paid']);

        foreach ([1, 2, 3] as $meeting) {
            $schedule = PaidSchedule::create([
                'course_transaction_id' => $transaction->id, 'teacher_id' => $teacher->id,
                'curriculum_lesson_id' => $lesson->id, 'meeting_number' => $meeting,
                'training_date' => today()->subDays(3 - $meeting), 'start_time' => '09:00', 'end_time' => '10:00',
                'zoom_url' => 'https://zoom.us/j/gaji-'.$meeting,
            ]);
            $this->actingAs($teacher)->post(route('pending-reports.store', $schedule), [
                'is_present' => '1', 'learning_summary' => 'Pertemuan selesai.',
            ])->assertRedirect(route('pending-reports.index'));
        }

        $olderSalary = \App\Models\TeacherSalary::latest('id')->firstOrFail();
        $olderSalary->update(['earned_at' => now()->subMonth()]);

        $this->actingAs($admin)->get(route('admin-teacher-salaries.index'))
            ->assertOk()->assertSee('Gaji Guru')->assertSee('Guru Gaji')->assertSee('Rp 250.000');
        $this->actingAs($admin)->get(route('admin-teacher-salaries.create', $teacher))
            ->assertOk()->assertSee('Bayar Gaji Guru')->assertSee('2 pertemuan')->assertSee('1234567890');

        $this->actingAs($admin)->post(route('admin-teacher-salaries.store', $teacher), [
            'month' => '2026-09', 'salary_ids' => [$olderSalary->id],
            'bank_name' => 'BCA', 'bank_account_number' => '1234567890',
            'bank_account_holder' => 'Guru Gaji', 'transfer_date' => today()->format('Y-m-d'),
            'transfer_proof' => UploadedFile::fake()->create('stale.pdf', 100, 'application/pdf'),
        ])->assertSessionHasErrors('salary');
        $this->assertDatabaseCount('teacher_payouts', 0);
        $this->assertCount(0, Storage::allFiles('teacher-payout-proofs'));

        $this->actingAs($admin)->post(route('admin-teacher-salaries.store', $teacher), [
            'month' => '2026-09',
            'salary_ids' => \App\Models\TeacherSalary::forMonth('2026-09')->pluck('id')->all(),
            'bank_name' => 'BCA', 'bank_account_number' => '1234567890',
            'bank_account_holder' => 'Guru Gaji', 'transfer_date' => today()->format('Y-m-d'),
            'transfer_proof' => UploadedFile::fake()->create('bukti.pdf', 100, 'application/pdf'),
            'notes' => 'Pembayaran seluruh honor.',
        ])->assertRedirect(route('admin-teacher-salaries.index', ['month' => '2026-09']));

        $payout = TeacherPayout::firstOrFail();
        $this->assertSame(250000, $payout->amount);
        $this->assertDatabaseCount('teacher_payouts', 1);
        $this->assertNull($olderSalary->fresh()->teacher_payout_id);
        $this->assertSame(2, $payout->salaries()->count());
        $this->actingAs($admin)->get(route('admin-teacher-salaries.report', ['month' => '2026-09']))
            ->assertOk()->assertSee('Rp 250.000')->assertSee('Sudah ditransfer');
        $this->actingAs($admin)->get(route('admin-teacher-salaries.report', ['month' => '2026-08']))
            ->assertOk()->assertSee('Rp 125.000')->assertSee('Belum dibayar');
        $this->actingAs($admin)->get(route('admin-teacher-salaries.index', ['month' => 'invalid']))
            ->assertSessionHasErrors('month');
        Storage::assertExists($payout->transfer_proof_path);
        $this->actingAs($admin)->get(route('admin-teacher-salaries.proof', $payout))->assertOk();
        $this->actingAs($teacher)->get(route('teacher-salaries.index'))
            ->assertOk()->assertSee('Sudah Dibayar')->assertSee('Lihat bukti transfer');
        $this->actingAs($teacher)->get(route('teacher-salaries.proof', $payout))->assertOk();
        $this->actingAs($teacher)->get(route('admin-teacher-salaries.report'))->assertForbidden();
        $this->actingAs($admin)->post(route('admin-teacher-salaries.store', $teacher), [
            'month' => '2026-09', 'salary_ids' => $payout->salaries()->pluck('id')->all(),
            'bank_name' => 'BCA', 'bank_account_number' => '1234567890',
            'bank_account_holder' => 'Guru Gaji', 'transfer_date' => today()->format('Y-m-d'),
            'transfer_proof' => UploadedFile::fake()->create('duplicate.pdf', 100, 'application/pdf'),
        ])->assertSessionHasErrors('salary');
        $this->assertDatabaseCount('teacher_payouts', 1);
        $this->assertCount(1, Storage::allFiles('teacher-payout-proofs'));

        $this->actingAs($admin)->get(route('admin-teacher-salaries.create', $teacher))->assertNotFound();
    }
}
