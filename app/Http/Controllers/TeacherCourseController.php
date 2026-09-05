<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\CourseCategory;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TeacherCourseController extends Controller
{
    public function index(Request $request): View
    {
        $teacherId = $request->user()->id;
        $search = trim((string) $request->query('search'));
        $categoryId = $request->integer('category');

        $categories = CourseCategory::query()
            ->whereHas('courses.teachers', fn ($query) => $query->where('users.id', $teacherId))
            ->orderBy('name')
            ->get();

        $courses = Course::query()
            ->with([
                'category',
                'sections.lessons',
                'transactions' => fn ($query) => $query
                    ->where('payment_status', 'paid')
                    ->with('student'),
            ])
            ->whereHas('teachers', fn ($query) => $query->where('users.id', $teacherId))
            ->when($categoryId, fn ($query) => $query->where('course_category_id', $categoryId))
            ->when($search, function ($query) use ($search) {
                $query->where(function ($query) use ($search) {
                    $query->where('name', 'like', "%{$search}%")
                        ->orWhere('code', 'like', "%{$search}%")
                        ->orWhere('description', 'like', "%{$search}%");
                });
            })
            ->orderByDesc('is_active')
            ->orderBy('sort_order')
            ->orderBy('name')
            ->paginate(9)
            ->withQueryString();

        return view('pages.teacher-courses.index', compact(
            'courses', 'categories', 'search', 'categoryId'
        ));
    }
}
