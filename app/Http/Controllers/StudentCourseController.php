<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\CourseCategory;
use App\Models\CourseTransaction;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class StudentCourseController extends Controller
{
    public function index(Request $request): View
    {
        $search = trim((string) $request->query('search'));
        $categoryId = $request->integer('category');

        $categories = CourseCategory::query()
            ->where('is_active', true)
            ->whereHas('courses', fn ($query) => $query->where('is_active', true))
            ->orderBy('name')
            ->get();

        $courses = Course::query()
            ->with(['category', 'teachers', 'sections.lessons'])
            ->where('is_active', true)
            ->whereHas('category', fn ($query) => $query->where('is_active', true))
            ->when($categoryId, fn ($query) => $query->where('course_category_id', $categoryId))
            ->when($search, function ($query) use ($search) {
                $query->where(function ($query) use ($search) {
                    $query->where('name', 'like', "%{$search}%")
                        ->orWhere('code', 'like', "%{$search}%")
                        ->orWhere('description', 'like', "%{$search}%");
                });
            })
            ->orderBy('sort_order')
            ->orderBy('name')
            ->paginate(9)
            ->withQueryString();

        return view('pages.student-courses.index', compact('categories', 'courses', 'search', 'categoryId'));
    }

    public function payment(Request $request, Course $course): View|RedirectResponse
    {
        $this->ensureCourseIsAvailable($course);

        $course->load(['category', 'sections.lessons']);

        return view('pages.student-courses.payment', [
            'course' => $course,
            'courseTotal' => $this->courseTotal($course),
        ]);
    }

    public function enroll(Request $request, Course $course): RedirectResponse
    {
        $this->ensureCourseIsAvailable($course);

        $validated = $request->validate([
            'sender_name' => ['required', 'string', 'max:255'],
            'sender_bank' => ['required', 'string', 'max:100'],
            'transfer_date' => ['required', 'date', 'before_or_equal:today'],
            'payment_proof' => ['required', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:2048'],
        ], [
            'payment_proof.required' => 'Bukti transfer wajib diunggah.',
            'payment_proof.mimes' => 'Bukti transfer harus berupa JPG, JPEG, PNG, atau PDF.',
            'payment_proof.max' => 'Ukuran bukti transfer maksimal 2 MB.',
        ]);

        $course->load('sections.lessons');
        $proofPath = $request->file('payment_proof')->store('payment-proofs');

        CourseTransaction::create([
            'course_id' => $course->id,
            'user_id' => $request->user()->id,
            'amount' => $this->courseTotal($course),
            'sender_name' => $validated['sender_name'],
            'sender_bank' => $validated['sender_bank'],
            'transfer_date' => $validated['transfer_date'],
            'payment_proof_path' => $proofPath,
            'payment_status' => 'pending',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return to_route('student-transactions.index')->with('success', 'Bukti transfer berhasil dikirim dan sedang menunggu verifikasi.');
    }

    public function transactions(Request $request): View
    {
        $transactions = $request->user()->courseTransactions()
            ->with('course.category')
            ->latest()
            ->paginate(10);

        return view('pages.student-transactions.index', compact('transactions'));
    }

    private function ensureCourseIsAvailable(Course $course): void
    {
        abort_unless($course->is_active && $course->category()->where('is_active', true)->exists(), 404);
    }

    private function courseTotal(Course $course): int
    {
        return (int) $course->sections->sum(
            fn ($section) => $section->lessons->sum(
                fn ($lesson) => $lesson->meetings * $lesson->fee_per_meeting
            )
        );
    }
}
