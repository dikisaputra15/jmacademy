<?php

namespace App\Http\Controllers;

use App\Models\CourseTransaction;
use App\Models\ParentReport;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class ParentReportController extends Controller
{
    public function index(Request $request): View
    {
        $search = trim((string) $request->query('search'));
        $classes = $this->completedClasses($request)
            ->with(['student', 'course.category', 'parentReport'])
            ->withCount('paidSchedules')
            ->when($search, fn ($query) => $query->where(fn ($query) => $query
                ->whereHas('student', fn ($student) => $student->where('name', 'like', "%{$search}%")->orWhere('email', 'like', "%{$search}%"))
                ->orWhereHas('course', fn ($course) => $course->where('name', 'like', "%{$search}%")->orWhere('code', 'like', "%{$search}%"))))
            ->latest('updated_at')->paginate(10)->withQueryString();

        return view('pages.parent-reports.index', compact('classes', 'search'));
    }

    public function create(Request $request, CourseTransaction $transaction): View
    {
        $this->ensureReportable($request, $transaction);
        $transaction->load([
            'student', 'course.category',
            'paidSchedules' => fn ($query) => $query->with(['teacher', 'lesson.section', 'learningReport'])->orderBy('training_date')->orderBy('start_time'),
        ]);

        return view('pages.parent-reports.create', compact('transaction'));
    }

    public function store(Request $request, CourseTransaction $transaction): RedirectResponse
    {
        $this->ensureReportable($request, $transaction);
        $validated = $request->validate([
            'grade' => ['required', Rule::in(['A', 'B', 'C', 'D'])],
            'understanding' => ['nullable', 'boolean'],
            'logic' => ['nullable', 'boolean'],
            'creativity' => ['nullable', 'boolean'],
            'strengths' => ['required', 'string', 'max:5000'],
            'improvements' => ['nullable', 'string', 'max:5000'],
            'recommendation' => ['nullable', 'string', 'max:5000'],
        ]);

        DB::transaction(fn () => ParentReport::create([
            ...$validated,
            'understanding' => $request->boolean('understanding'),
            'logic' => $request->boolean('logic'),
            'creativity' => $request->boolean('creativity'),
            'course_transaction_id' => $transaction->id,
            'teacher_id' => $request->user()->id,
            'student_id' => $transaction->user_id,
            'course_id' => $transaction->course_id,
            'submitted_at' => now(),
        ]));

        return to_route('parent-reports.index')->with('success', 'Parent report berhasil disimpan.');
    }

    private function completedClasses(Request $request): Builder
    {
        return CourseTransaction::query()
            ->where('payment_status', 'paid')
            ->whereHas('paidSchedules', fn ($query) => $query->where('teacher_id', $request->user()->id))
            ->whereDoesntHave('paidSchedules', fn ($query) => $query->whereDoesntHave('learningReport'));
    }

    private function ensureReportable(Request $request, CourseTransaction $transaction): void
    {
        $allowed = $this->completedClasses($request)->whereKey($transaction->id)->exists();
        abort_unless($allowed, 404);
        if ($transaction->parentReport()->exists()) {
            throw ValidationException::withMessages(['report' => 'Parent report untuk kelas ini sudah pernah dibuat.']);
        }
    }
}
