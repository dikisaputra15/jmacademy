<?php

namespace App\Http\Controllers;

use App\Models\ParentReport;
use BaconQrCode\Renderer\Image\SvgImageBackEnd;
use BaconQrCode\Renderer\ImageRenderer;
use BaconQrCode\Renderer\RendererStyle\RendererStyle;
use BaconQrCode\Writer;
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


        $certificateUrl = route('student-reports.certificate', $report);
        $writer = new Writer(new ImageRenderer(new RendererStyle(160), new SvgImageBackEnd()));
        $certificateQr = 'data:image/svg+xml;base64,'.base64_encode($writer->writeString($certificateUrl));

        return view('pages.student-reports.certificate', compact('report', 'certificateUrl', 'certificateQr'));
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
