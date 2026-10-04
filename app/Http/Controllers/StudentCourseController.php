<?php

namespace App\Http\Controllers;

use App\Models\ClassCategory;
use App\Models\Course;
use App\Models\CourseCategory;
use App\Models\CourseTransaction;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Validation\Rule;

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
            ->with(['category', 'sections.lessons'])
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
            'classCategories' => ClassCategory::orderBy('id')->get(),
        ]);
    }

    public function enroll(Request $request, Course $course): RedirectResponse
    {
        $this->ensureCourseIsAvailable($course);

        $validated = $request->validate([
            'class_category_id' => ['required', 'integer', Rule::exists('class_categories', 'id')->whereNotNull('fee_per_meeting')],
            'sender_name' => ['required', 'string', 'max:255'],
            'sender_bank' => ['required', 'string', 'max:100'],
            'transfer_date' => ['required', 'date', 'before_or_equal:today'],
            'payment_proof' => ['required', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:2048'],
        ], [
            'class_category_id.required' => 'Pilih kategori kelas terlebih dahulu.',
            'class_category_id.exists' => 'Kategori kelas belum tersedia untuk dipesan.',
            'payment_proof.required' => 'Bukti transfer wajib diunggah.',
            'payment_proof.mimes' => 'Bukti transfer harus berupa JPG, JPEG, PNG, atau PDF.',
            'payment_proof.max' => 'Ukuran bukti transfer maksimal 2 MB.',
        ]);

        $classCategory = ClassCategory::findOrFail($validated['class_category_id']);
        $proofPath = $request->file('payment_proof')->store('payment-proofs');

        CourseTransaction::create([
            'course_id' => $course->id,
            'user_id' => $request->user()->id,
            'class_category_id' => $classCategory->id,
            'amount' => $classCategory->fee_per_meeting,
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
            ->with(['course.category', 'classCategory'])
            ->latest()
            ->paginate(10);

        return view('pages.student-transactions.index', compact('transactions'));
    }

    private function ensureCourseIsAvailable(Course $course): void
    {
        abort_unless($course->is_active && $course->category()->where('is_active', true)->exists(), 404);
    }

}
