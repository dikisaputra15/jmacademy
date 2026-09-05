<?php

namespace App\Http\Controllers;

use App\Models\LearningReport;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TeacherHistoryController extends Controller
{
    public function index(Request $request): View
    {
        $search = trim((string) $request->query('search'));
        $histories = LearningReport::query()
            ->where('teacher_id', $request->user()->id)
            ->with([
                'student', 'course.category', 'lesson.section',
                'schedule', 'salary',
            ])
            ->when($search, fn ($query) => $query->where(fn ($query) => $query
                ->where('learning_summary', 'like', "%{$search}%")
                ->orWhere('notes', 'like', "%{$search}%")
                ->orWhereHas('student', fn ($student) => $student->where('name', 'like', "%{$search}%")->orWhere('email', 'like', "%{$search}%"))
                ->orWhereHas('course', fn ($course) => $course->where('name', 'like', "%{$search}%")->orWhere('code', 'like', "%{$search}%"))
                ->orWhereHas('lesson', fn ($lesson) => $lesson->where('title', 'like', "%{$search}%"))))
            ->latest('submitted_at')
            ->paginate(10)
            ->withQueryString();

        return view('pages.teacher-histories.index', compact('histories', 'search'));
    }
}
