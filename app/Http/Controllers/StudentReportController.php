<?php

namespace App\Http\Controllers;

use App\Models\ParentReport;
use Illuminate\Http\Request;
use Illuminate\View\View;

class StudentReportController extends Controller
{
    public function index(Request $request): View
    {
        $search = trim((string) $request->query('search'));
        $reports = ParentReport::query()
            ->where('student_id', $request->user()->id)
            ->with(['teacher', 'course.category', 'transaction'])
            ->when($search, fn ($query) => $query->where(fn ($query) => $query
                ->where('grade', 'like', "%{$search}%")
                ->orWhereHas('teacher', fn ($teacher) => $teacher->where('name', 'like', "%{$search}%"))
                ->orWhereHas('course', fn ($course) => $course->where('name', 'like', "%{$search}%")->orWhere('code', 'like', "%{$search}%"))))
            ->latest('submitted_at')->paginate(10)->withQueryString();

        return view('pages.student-reports.index', compact('reports', 'search'));
    }

    public function show(Request $request, ParentReport $report): View
    {
        $this->authorizeOwner($request, $report);
        $this->loadReport($report);

        return view('pages.student-reports.show', compact('report'));
    }

    public function certificate(Request $request, ParentReport $report): View
    {
        $this->authorizeOwner($request, $report);
        $report->load(['student', 'teacher', 'course.category', 'transaction']);


        return view('pages.student-reports.certificate', compact('report'));
    }

    private function authorizeOwner(Request $request, ParentReport $report): void
    {
        abort_unless($report->student_id === $request->user()->id, 404);
    }

    private function loadReport(ParentReport $report): void
    {
        $report->load([
            'student', 'teacher', 'course.category',
            'transaction.paidSchedules' => fn ($query) => $query
                ->with(['lesson.section', 'learningReport'])
                ->orderBy('training_date')->orderBy('start_time'),
        ]);
    }
}
