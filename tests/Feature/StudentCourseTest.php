<?php

namespace Tests\Feature;

use App\Models\ClassCategory;
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

        ClassCategory::where('name', 'Reguler A')->update(['fee_per_meeting' => 50000]);
        ClassCategory::where('name', 'Private')->update(['fee_per_meeting' => 200000]);

        $this->actingAs($student)
            ->get(route('student-courses.index'))
            ->assertOk()
            ->assertSee('English Starter')
            ->assertSee('Introduction')
            ->assertSee('Greetings and Introductions')
            ->assertDontSee('Total biaya course')
            ->assertDontSee('Rp 50.000')
            ->assertDontSee('Rp 250.000')
            ->assertSee('Ikuti Kelas')
            ->assertDontSee($inactiveCourse->name);

        ClassCategory::query()->update(['fee_per_meeting' => null]);
        $this->get(route('student-courses.index'))->assertOk()
            ->assertDontSee('Biaya belum tersedia')->assertDontSee('Rp 0');

        ClassCategory::where('name', 'Reguler A')->update(['fee_per_meeting' => 0]);
        $this->get(route('student-courses.index'))->assertOk()
            ->assertDontSee('Rp 0')->assertDontSee('Biaya belum tersedia');
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
            ->assertSee('Kategori Kelas')
            ->assertSee('Upload bukti transfer');

        $classCategory = ClassCategory::firstOrFail();
        $classCategory->update(['fee_per_meeting' => 125000]);
        $paymentData = [
            'class_category_id' => $classCategory->id,
            'sender_name' => 'Student Test',
            'sender_bank' => 'BCA',
            'transfer_date' => now()->toDateString(),
            'payment_proof' => UploadedFile::fake()->image('transfer.jpg'),
        ];
        $this->actingAs($student)->post(route('student-courses.enroll', $course), array_diff_key($paymentData, ['class_category_id' => true]))->assertSessionHasErrors('class_category_id');
        $this->actingAs($student)->post(route('student-courses.enroll', $course), [...$paymentData, 'class_category_id' => ClassCategory::whereNull('fee_per_meeting')->firstOrFail()->id])->assertSessionHasErrors('class_category_id');
        $this->assertDatabaseCount('course_student', 0);
        $this->actingAs($student)
            ->post(route('student-courses.enroll', $course), [...$paymentData, 'amount' => 1])
            ->assertRedirect(route('student-transactions.index'));
        $classCategory->update(['fee_per_meeting' => 150000]);
        $this->actingAs($student)->post(route('student-courses.enroll', $course), [
            ...$paymentData,
            'payment_proof' => UploadedFile::fake()->image('transfer-again.jpg'),
        ])->assertRedirect(route('student-transactions.index'));

        $this->assertDatabaseCount('course_student', 2);
        $this->assertDatabaseHas('course_student', ['course_id' => $course->id, 'amount' => 150000]);
        $payment = $student->courseTransactions()->firstOrFail();
        $this->assertSame(125000, $payment->amount);
        $this->assertSame('pending', $payment->payment_status);
        $this->assertSame($classCategory->id, $payment->class_category_id);
        Storage::disk('local')->assertExists($payment->payment_proof_path);
        $this->actingAs($student)
            ->get(route('student-courses.index'))
            ->assertSee('Ikuti Kelas')
            ->assertDontSee('Menunggu Verifikasi');
        $this->actingAs($student)
            ->get(route('student-transactions.index'))
            ->assertOk()
            ->assertSee('Menunggu Verifikasi')
            ->assertSee('Rp 125.000');
    }
}
