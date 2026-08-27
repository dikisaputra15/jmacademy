<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\CurriculumLesson;
use App\Models\CurriculumSection;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CurriculumController extends Controller
{
    public function edit(Course $course): View
    {
        return view('pages.courses.curriculum', ['course' => $course->load(['category', 'sections.lessons'])]);
    }

    public function storeSection(Request $request, Course $course): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'sort_order' => ['required', 'integer', 'min:0'],
        ]);

        $course->sections()->create($validated);

        return back()->with('success', 'Section curriculum berhasil ditambahkan.');
    }

    public function updateSection(Request $request, CurriculumSection $section): RedirectResponse
    {
        $section->update($request->validate([
            'name' => ['required', 'string', 'max:255'],
            'sort_order' => ['required', 'integer', 'min:0'],
        ]));

        return back()->with('success', 'Section berhasil diperbarui.');
    }

    public function destroySection(CurriculumSection $section): RedirectResponse
    {
        $section->delete();

        return back()->with('success', 'Section dan seluruh lesson berhasil dihapus.');
    }

    public function storeLesson(Request $request, CurriculumSection $section): RedirectResponse
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'meetings' => ['required', 'integer', 'min:1', 'max:999'],
            'sort_order' => ['required', 'integer', 'min:0'],
        ]);

        $section->lessons()->create($validated);

        return back()->with('success', 'Lesson berhasil ditambahkan.');
    }

    public function destroyLesson(CurriculumLesson $lesson): RedirectResponse
    {
        $lesson->delete();

        return back()->with('success', 'Lesson berhasil dihapus.');
    }
}
