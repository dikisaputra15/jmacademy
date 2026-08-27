<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\CourseCategory;
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
}
