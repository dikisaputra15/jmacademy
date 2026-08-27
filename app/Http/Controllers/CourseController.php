<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\CourseCategory;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class CourseController extends Controller
{
    public function index(Request $request): View
    {
        $categoryId = $request->integer('category');
        $categories = CourseCategory::query()->where('is_active', true)->orderBy('name')->get();
        $selectedCategory = $categories->firstWhere('id', $categoryId) ?? $categories->first();

        $courses = Course::query()
            ->with(['category', 'teachers', 'sections.lessons'])
            ->when($selectedCategory, fn ($query) => $query->whereBelongsTo($selectedCategory, 'category'))
            ->orderBy('sort_order')
            ->orderBy('name')
            ->paginate(10)
            ->withQueryString();

        return view('pages.courses.index', compact('categories', 'selectedCategory', 'courses'));
    }

    public function create(): View
    {
        return view('pages.courses.create', [
            'categories' => CourseCategory::where('is_active', true)->orderBy('name')->get(),
            'suggestedCode' => $this->generateCourseCode(),
            'teachers' => $this->teachers(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        [$validated, $teacherIds] = $this->validatedCourseData($request);
        $validated['code'] = $this->generateCourseCode();

        $course = DB::transaction(function () use ($validated, $teacherIds) {
            $course = Course::create($validated);
            $course->teachers()->sync($teacherIds);

            return $course;
        });

        return to_route('courses.curriculum', $course)->with('success', 'Course dibuat. Silakan tambahkan curriculum.');
    }

    public function edit(Course $course): View
    {
        return view('pages.courses.edit', [
            'course' => $course->load('teachers'),
            'categories' => CourseCategory::orderBy('name')->get(),
            'teachers' => $this->teachers(),
        ]);
    }

    public function update(Request $request, Course $course): RedirectResponse
    {
        [$validated, $teacherIds] = $this->validatedCourseData($request, $course);

        DB::transaction(function () use ($course, $validated, $teacherIds) {
            $course->update($validated);
            $course->teachers()->sync($teacherIds);
        });

        return to_route('courses.index', ['category' => $course->course_category_id])->with('success', 'Course berhasil diperbarui.');
    }

    public function toggleStatus(Course $course): RedirectResponse
    {
        $course->update(['is_active' => ! $course->is_active]);

        return back()->with('success', 'Status course berhasil diperbarui.');
    }

    public function destroy(Course $course): RedirectResponse
    {
        $categoryId = $course->course_category_id;
        $course->delete();

        return to_route('courses.index', ['category' => $categoryId])->with('success', 'Course dan seluruh curriculum berhasil dihapus.');
    }

    private function validatedCourseData(Request $request, ?Course $course = null): array
    {
        $validated = $request->validate([
            'course_category_id' => ['required', Rule::exists('course_categories', 'id')],
            'name' => ['required', 'string', 'max:255'],
            'age_min' => ['nullable', 'integer', 'min:3', 'max:99'],
            'age_max' => ['nullable', 'integer', 'min:3', 'max:99', 'gte:age_min'],
            'description' => ['nullable', 'string', 'max:2000'],
            'is_active' => ['required', 'boolean'],
            'sort_order' => ['required', 'integer', 'min:0'],
            'teacher_ids' => ['nullable', 'array'],
            'teacher_ids.*' => ['integer', 'distinct', Rule::exists('users', 'id')],
        ]);

        $teacherIds = collect($validated['teacher_ids'] ?? [])->map(fn ($id) => (int) $id)->unique()->values();
        $validTeacherCount = User::role('guru')->whereIn('id', $teacherIds)->count();

        if ($validTeacherCount !== $teacherIds->count()) {
            throw ValidationException::withMessages([
                'teacher_ids' => 'Semua pengajar yang dipilih harus memiliki role guru.',
            ]);
        }

        unset($validated['teacher_ids']);

        return [$validated, $teacherIds->all()];
    }

    private function teachers()
    {
        return User::role('guru')->orderBy('name')->get();
    }

    private function generateCourseCode(): string
    {
        $lastNumber = Course::query()
            ->where('code', 'like', 'CRS-%')
            ->pluck('code')
            ->map(function (string $code): int {
                return preg_match('/^CRS-(\d+)$/', $code, $matches)
                    ? (int) $matches[1]
                    : 0;
            })
            ->max() ?? 0;

        do {
            $lastNumber++;
            $code = 'CRS-'.str_pad((string) $lastNumber, 4, '0', STR_PAD_LEFT);
        } while (Course::where('code', $code)->exists());

        return $code;
    }
}
