<?php

namespace App\Http\Controllers;

use App\Models\LearningReport;
use App\Models\PaidSchedule;
use App\Models\TeacherSalary;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class PendingReportController extends Controller
{
    public function index(Request $request): View
    {
        $search = trim((string) $request->query('search'));
        $schedules = $this->pendingQuery($request)
            ->with(['transaction.student', 'transaction.course.category', 'lesson.section'])
            ->when($search, fn ($query) => $query->where(fn ($query) => $query
                ->whereHas('transaction.student', fn ($student) => $student->where('name', 'like', "%{$search}%")->orWhere('email', 'like', "%{$search}%"))
                ->orWhereHas('transaction.course', fn ($course) => $course->where('name', 'like', "%{$search}%")->orWhere('code', 'like', "%{$search}%"))
                ->orWhereHas('lesson', fn ($lesson) => $lesson->where('title', 'like', "%{$search}%"))))
            ->orderByDesc('training_date')->orderByDesc('end_time')->paginate(10)->withQueryString();

        return view('pages.pending-reports.index', compact('schedules', 'search'));
    }

    public function select(Request $request): View|RedirectResponse
    {
        $schedules = $this->pendingQuery($request)
            ->with(['transaction.student', 'transaction.course', 'lesson.section'])
            ->orderByDesc('training_date')
            ->orderByDesc('end_time')
            ->get();

        if ($request->filled('schedule_id')) {
            $schedule = $schedules->firstWhere('id', $request->integer('schedule_id'));

            if (! $schedule) {
                return back()->withErrors(['schedule_id' => 'Pilih pembelajaran yang tersedia untuk dibuatkan report.']);
            }

            return to_route('pending-reports.create', $schedule);
        }

        return view('pages.pending-reports.select', compact('schedules'));
    }

    public function create(Request $request, PaidSchedule $schedule): View
    {
        $this->ensureReportable($request, $schedule);
        $schedule->load(['transaction.student', 'transaction.course.category', 'lesson.section']);

        return view('pages.pending-reports.create', compact('schedule'));
    }

    public function store(Request $request, PaidSchedule $schedule): RedirectResponse
    {
        $this->ensureReportable($request, $schedule);
        $validated = $request->validate([
            'is_present' => ['required', 'boolean'],
            'learning_summary' => ['required', 'string', 'max:5000'],
            'notes' => ['nullable', 'string', 'max:5000'],
            'video_title' => ['nullable', 'string', 'max:255', 'required_with:video_url'],
            'video_url' => ['nullable', 'url:http,https', 'max:1000', 'required_with:video_title'],
        ]);
        $schedule->loadMissing(['transaction', 'lesson']);

        DB::transaction(function () use ($validated, $schedule, $request) {
            $submittedAt = now();
            $report = LearningReport::create([
                ...$validated,
                'paid_schedule_id' => $schedule->id,
                'teacher_id' => $request->user()->id,
                'student_id' => $schedule->transaction->user_id,
                'course_id' => $schedule->transaction->course_id,
                'curriculum_lesson_id' => $schedule->curriculum_lesson_id,
                'submitted_at' => $submittedAt,
            ]);

            TeacherSalary::create([
                'learning_report_id' => $report->id,
                'teacher_id' => $request->user()->id,
                'student_id' => $schedule->transaction->user_id,
                'course_id' => $schedule->transaction->course_id,
                'paid_schedule_id' => $schedule->id,
                'amount' => $schedule->lesson?->fee_per_meeting ?? 0,
                'earned_at' => $submittedAt,
            ]);
        });

        return to_route('pending-reports.index')->with('success', 'Report pembelajaran berhasil disimpan.');
    }

    private function pendingQuery(Request $request)
    {
        return PaidSchedule::query()
            ->where('teacher_id', $request->user()->id)
            ->whereDoesntHave('learningReport')
            ->where(function ($query) {
                $query->whereDate('training_date', '<', today())
                    ->orWhere(fn ($query) => $query->whereDate('training_date', today())->whereTime('end_time', '<=', now()->format('H:i:s')));
            });
    }

    private function ensureReportable(Request $request, PaidSchedule $schedule): void
    {
        abort_unless($schedule->teacher_id === $request->user()->id, 404);
        $isCompleted = $schedule->training_date->isBefore(today())
            || ($schedule->training_date->isToday() && $schedule->end_time <= now()->format('H:i:s'));
        if (! $isCompleted) throw ValidationException::withMessages(['schedule' => 'Report hanya dapat diisi setelah pembelajaran selesai.']);
        if ($schedule->learningReport()->exists()) throw ValidationException::withMessages(['schedule' => 'Report untuk pembelajaran ini sudah pernah dibuat.']);
    }
}
