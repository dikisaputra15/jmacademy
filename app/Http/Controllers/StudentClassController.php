<?php

namespace App\Http\Controllers;

use App\Models\CourseTransaction;
use App\Models\PaidSchedule;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class StudentClassController extends Controller
{
    public function index(Request $request): View
    {
        $search = trim((string) $request->query('search'));

        $classes = CourseTransaction::query()
            ->with([
                'course.category',
                'course.teachers',
                'course.sections.lessons',
                'paidSchedules.teacher',
                'paidSchedules.lesson.section',
            ])
            ->where('user_id', $request->user()->id)
            ->where('payment_status', 'paid')
            ->when($search, fn ($query) => $query->whereHas('course', fn ($course) => $course
                ->where('name', 'like', "%{$search}%")
                ->orWhere('code', 'like', "%{$search}%")))
            ->latest('verified_at')
            ->paginate(10)
            ->withQueryString();

        return view('pages.student-classes.index', compact('classes', 'search'));
    }

    public function join(Request $request, PaidSchedule $schedule): RedirectResponse
    {
        $schedule->loadMissing('transaction');

        abort_unless(
            $schedule->transaction
            && $schedule->transaction->user_id === $request->user()->id
            && $schedule->transaction->payment_status === 'paid',
            404
        );

        if ($schedule->training_date->isAfter(today())) {
            return back()->with('error', 'Link Zoom baru dapat dibuka pada tanggal training.');
        }

        if ($schedule->hasEnded()) {
            return back()->with('error', 'Jadwal training sudah selesai. Link Zoom tidak dapat dibuka lagi.');
        }

        if (! $schedule->zoom_url) {
            return back()->with('error', 'Link Zoom belum tersedia untuk jadwal ini.');
        }

        return redirect()->away($schedule->zoom_url);
    }
}
