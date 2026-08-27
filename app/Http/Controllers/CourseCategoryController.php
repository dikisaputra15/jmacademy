<?php

namespace App\Http\Controllers;

use App\Models\CourseCategory;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class CourseCategoryController extends Controller
{
    public function index(Request $request): View
    {
        $search = trim((string) $request->query('search'));

        $categories = CourseCategory::query()
            ->when($search, function ($query) use ($search) {
                $query->where(function ($query) use ($search) {
                    $query->where('name', 'like', "%{$search}%")
                        ->orWhere('description', 'like', "%{$search}%");
                });
            })
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('pages.course-categories.index', compact('categories', 'search'));
    }

    public function create(): View
    {
        return view('pages.course-categories.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validateCategory($request);
        $validated['slug'] = $this->uniqueSlug($validated['name']);

        CourseCategory::create($validated);

        return to_route('course-categories.index')->with('success', 'Category course berhasil ditambahkan.');
    }

    public function edit(CourseCategory $courseCategory): View
    {
        return view('pages.course-categories.edit', compact('courseCategory'));
    }

    public function update(Request $request, CourseCategory $courseCategory): RedirectResponse
    {
        $validated = $this->validateCategory($request, $courseCategory);

        if ($validated['name'] !== $courseCategory->name) {
            $validated['slug'] = $this->uniqueSlug($validated['name'], $courseCategory);
        }

        $courseCategory->update($validated);

        return to_route('course-categories.index')->with('success', 'Category course berhasil diperbarui.');
    }

    public function toggleStatus(CourseCategory $courseCategory): RedirectResponse
    {
        $courseCategory->update(['is_active' => ! $courseCategory->is_active]);

        $status = $courseCategory->is_active ? 'diaktifkan' : 'dinonaktifkan';

        return back()->with('success', "Category course berhasil {$status}.");
    }

    public function destroy(CourseCategory $courseCategory): RedirectResponse
    {
        if ($courseCategory->courses()->exists()) {
            return back()->with('error', 'Category tidak dapat dihapus karena masih memiliki course.');
        }

        $courseCategory->delete();

        return to_route('course-categories.index')->with('success', 'Category course berhasil dihapus.');
    }

    private function validateCategory(Request $request, ?CourseCategory $category = null): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:255', Rule::unique('course_categories')->ignore($category)],
            'description' => ['nullable', 'string', 'max:1000'],
            'is_active' => ['required', 'boolean'],
        ]);
    }

    private function uniqueSlug(string $name, ?CourseCategory $except = null): string
    {
        $baseSlug = Str::slug($name) ?: 'category';
        $slug = $baseSlug;
        $counter = 2;

        while (CourseCategory::where('slug', $slug)
            ->when($except, fn ($query) => $query->whereKeyNot($except->getKey()))
            ->exists()) {
            $slug = "{$baseSlug}-{$counter}";
            $counter++;
        }

        return $slug;
    }
}
