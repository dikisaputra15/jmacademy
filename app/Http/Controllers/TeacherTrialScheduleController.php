<?php

namespace App\Http\Controllers;

use App\Models\TrialSchedule;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TeacherTrialScheduleController extends Controller
{
    public function index(Request $request): View
    {
        $filter = (string) $request->query('filter', 'upcoming');
        $search = trim((string) $request->query('search'));
        if (! in_array($filter, ['upcoming', 'today', 'past', 'all'], true)) $filter = 'upcoming';

        $baseQuery = TrialSchedule::query()->where('teacher_id', $request->user()->id);
        $counts = [
            'upcoming' => (clone $baseQuery)->whereDate('training_date', '>=', today())->count(),
            'today' => (clone $baseQuery)->whereDate('training_date', today())->count(),
            'past' => (clone $baseQuery)->whereDate('training_date', '<', today())->count(),
            'all' => (clone $baseQuery)->count(),
        ];

        $weekStart = today()->startOfWeek();
        $requestedWeek = (string) $request->query('week');
        if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $requestedWeek)) {
            try {
                $parsedWeek = Carbon::createFromFormat('Y-m-d', $requestedWeek);
                if ($parsedWeek->format('Y-m-d') === $requestedWeek) $weekStart = $parsedWeek->startOfWeek();
            } catch (\Throwable) {
                // Gunakan minggu ini untuk parameter yang tidak valid.
            }
        }
        $weekEnd = $weekStart->copy()->endOfWeek();
        $days = collect(range(0, 6))->map(fn (int $day) => $weekStart->copy()->addDays($day));
        $timeSlots = collect(range(0, 47))->map(fn (int $slot) => sprintf('%02d:%02d', intdiv($slot * 30, 60), ($slot * 30) % 60));
        $calendarSchedules = (clone $baseQuery)->with(['student', 'course'])->whereBetween('training_date', [$weekStart->toDateString(), $weekEnd->toDateString()])->orderBy('training_date')->orderBy('start_time')->get();

        $schedules = (clone $baseQuery)->with(['student', 'course.category', 'lesson.section'])
            ->when($filter === 'upcoming', fn ($query) => $query->whereDate('training_date', '>=', today()))
            ->when($filter === 'today', fn ($query) => $query->whereDate('training_date', today()))
            ->when($filter === 'past', fn ($query) => $query->whereDate('training_date', '<', today()))
            ->when($search, fn ($query) => $query->where(fn ($query) => $query
                ->whereHas('student', fn ($student) => $student->where('name', 'like', "%{$search}%")->orWhere('email', 'like', "%{$search}%"))
                ->orWhereHas('course', fn ($course) => $course->where('name', 'like', "%{$search}%")->orWhere('code', 'like', "%{$search}%"))
                ->orWhereHas('lesson', fn ($lesson) => $lesson->where('title', 'like', "%{$search}%"))))
            ->orderBy('training_date', $filter === 'past' ? 'desc' : 'asc')->orderBy('start_time', $filter === 'past' ? 'desc' : 'asc')->paginate(12)->withQueryString();

        return view('pages.teacher-trial-schedules.index', compact('schedules', 'calendarSchedules', 'counts', 'filter', 'search', 'weekStart', 'weekEnd', 'days', 'timeSlots'));
    }

    public function join(Request $request, TrialSchedule $schedule): RedirectResponse
    {
        abort_unless($schedule->teacher_id === $request->user()->id, 404);
        if ($schedule->training_date->isAfter(today())) return back()->with('error', 'Link Zoom baru dapat dibuka pada tanggal trial.');
        if ($schedule->hasEnded()) return back()->with('error', 'Jadwal trial sudah selesai. Link Zoom tidak dapat dibuka lagi.');
        if (! $schedule->zoom_url) return back()->with('error', 'Link Zoom belum tersedia untuk jadwal ini.');

        return redirect()->away($schedule->zoom_url);
    }
}
