<?php

namespace Tests\Feature;

use App\Models\Course;
use App\Models\CourseCategory;
use App\Models\CurriculumLesson;
use App\Models\CurriculumSection;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class StudentCourseTest extends TestCase
{
    use RefreshDatabase;

    public function test_student_sees_active_courses_and_curriculum_only(): void
    {
        Role::create(['name' => 'student']);
        $student = User::factory()->create();
        $student->assignRole('student');

        $category = CourseCategory::create([
            'name' => 'English', 'slug' => 'english', 'is_active' => true,
        ]);
        $activeCourse = Course::create([
            'course_category_id' => $category->id,
            'name' => 'English Starter',
            'code' => 'CRS-0001',
            'is_active' => true,
        ]);
        $inactiveCourse = Course::create([
            'course_category_id' => $category->id,
            'name' => 'Hidden Course',
            'code' => 'CRS-0002',
            'is_active' => false,
        ]);
        $section = CurriculumSection::create(['course_id' => $activeCourse->id, 'name' => 'Introduction']);
        CurriculumLesson::create([
            'curriculum_section_id' => $section->id,
            'title' => 'Greetings and Introductions',
            'meetings' => 2,
            'fee_per_meeting' => 125000,
        ]);

        $this->actingAs($student)
            ->get(route('student-courses.index'))
            ->assertOk()
            ->assertSee('English Starter')
            ->assertSee('Introduction')
            ->assertSee('Greetings and Introductions')
            ->assertSee('Rp 250.000')
            ->assertSee('Ikuti Kelas')
            ->assertDontSee($inactiveCourse->name);
    }

    public function test_non_student_cannot_open_student_course_catalog(): void
    {
        Role::create(['name' => 'admin']);
        $admin = User::factory()->create();
        $admin->assignRole('admin');

        $this->actingAs($admin)
            ->get(route('student-courses.index'))
            ->assertForbidden();
    }

    public function test_student_can_order_the_same_course_repeatedly_and_view_transactions(): void
    {
        Storage::fake('local');
        Role::create(['name' => 'student']);
        $student = User::factory()->create();
        $student->assignRole('student');
        $category = CourseCategory::create(['name' => 'English', 'slug' => 'english', 'is_active' => true]);
        $course = Course::create([
            'course_category_id' => $category->id,
            'name' => 'English Starter',
            'code' => 'CRS-0001',
            'is_active' => true,
        ]);
        $section = CurriculumSection::create(['course_id' => $course->id, 'name' => 'Introduction']);
        CurriculumLesson::create([
            'curriculum_section_id' => $section->id,
            'title' => 'Greetings',
            'meetings' => 2,
            'fee_per_meeting' => 125000,
        ]);

        $this->actingAs($student)
            ->get(route('student-courses.payment', $course))
            ->assertOk()
            ->assertSee('Rp 250.000')
            ->assertSee('Upload bukti transfer');

        $paymentData = [
            'sender_name' => 'Student Test',
            'sender_bank' => 'BCA',
            'transfer_date' => now()->toDateString(),
            'payment_proof' => UploadedFile::fake()->image('transfer.jpg'),
        ];
        $this->actingAs($student)
            ->post(route('student-courses.enroll', $course), $paymentData)
            ->assertRedirect(route('student-transactions.index'));
        $this->actingAs($student)->post(route('student-courses.enroll', $course), [
            ...$paymentData,
            'payment_proof' => UploadedFile::fake()->image('transfer-again.jpg'),
        ])->assertRedirect(route('student-transactions.index'));

        $this->assertDatabaseCount('course_student', 2);
        $payment = $student->courseTransactions()->firstOrFail();
        $this->assertSame(250000, $payment->amount);
        $this->assertSame('pending', $payment->payment_status);
        Storage::disk('local')->assertExists($payment->payment_proof_path);
        $this->actingAs($student)
            ->get(route('student-courses.index'))
            ->assertSee('Ikuti Kelas')
            ->assertDontSee('Menunggu Verifikasi');
        $this->actingAs($student)
            ->get(route('student-transactions.index'))
            ->assertOk()
            ->assertSee('Menunggu Verifikasi')
            ->assertSee('Rp 250.000');
    }
}
