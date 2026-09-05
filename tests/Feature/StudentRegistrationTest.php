<?php

namespace Tests\Feature;

use App\Models\Course;
use App\Models\CourseCategory;
use App\Models\CourseTransaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class StudentRegistrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_see_and_verify_pending_student_payment(): void
    {
        [$admin, $student, $transaction] = $this->registrationData();

        $this->actingAs($admin)
            ->get(route('student-registrations.index'))
            ->assertOk()
            ->assertSee($student->name)
            ->assertSee($transaction->course->name)
            ->assertSee('TRX-'.str_pad((string) $transaction->id, 6, '0', STR_PAD_LEFT));

        $this->actingAs($admin)
            ->patch(route('student-registrations.verify', $transaction), [
                'payment_status' => 'paid',
            ])
            ->assertRedirect()
            ->assertSessionHas('success');

        $this->assertDatabaseHas('course_student', [
            'id' => $transaction->id,
            'payment_status' => 'paid',
            'verified_by' => $admin->id,
        ]);
    }

    public function test_rejection_requires_a_reason(): void
    {
        [$admin, , $transaction] = $this->registrationData();

        $this->actingAs($admin)
            ->patch(route('student-registrations.verify', $transaction), [
                'payment_status' => 'rejected',
            ])
            ->assertSessionHasErrors('verification_note');

        $this->assertSame('pending', $transaction->fresh()->payment_status);
    }

    public function test_student_cannot_access_registration_verification(): void
    {
        [, $student] = $this->registrationData();

        $this->actingAs($student)
            ->get(route('student-registrations.index'))
            ->assertForbidden();
    }

    public function test_admin_can_view_payment_proof(): void
    {
        Storage::fake();
        [$admin, , $transaction] = $this->registrationData();
        Storage::put($transaction->payment_proof_path, 'fake-image-content');

        $this->actingAs($admin)
            ->get(route('student-registrations.proof', $transaction))
            ->assertOk();
    }

    private function registrationData(): array
    {
        Role::findOrCreate('admin');
        Role::findOrCreate('student');

        $admin = User::factory()->create();
        $admin->assignRole('admin');
        $student = User::factory()->create();
        $student->assignRole('student');
        $category = CourseCategory::create([
            'name' => 'English', 'slug' => 'english', 'is_active' => true,
        ]);
        $course = Course::create([
            'course_category_id' => $category->id,
            'name' => 'English Starter',
            'code' => 'CRS-1001',
            'is_active' => true,
        ]);
        $transaction = CourseTransaction::create([
            'course_id' => $course->id,
            'user_id' => $student->id,
            'amount' => 500000,
            'sender_name' => 'Orang Tua Student',
            'sender_bank' => 'BCA',
            'transfer_date' => now()->toDateString(),
            'payment_proof_path' => 'payment-proofs/example.jpg',
            'payment_status' => 'pending',
        ]);

        return [$admin, $student, $transaction];
    }
}
