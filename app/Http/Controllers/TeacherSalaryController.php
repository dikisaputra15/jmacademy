<?php

namespace App\Http\Controllers;

use App\Models\TeacherSalary;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class TeacherSalaryController extends Controller
{
    public function index(Request $request): View
    {
        $teacherId = $request->user()->id;
        $search = trim((string) $request->query('search'));
        $baseQuery = TeacherSalary::query()->where('teacher_id', $teacherId);

        $totalSalary = (clone $baseQuery)->sum('amount');
        $monthlySalary = (clone $baseQuery)
            ->whereBetween('earned_at', [now()->startOfMonth(), now()->endOfMonth()])
            ->sum('amount');
        $totalMeetings = (clone $baseQuery)->count();
        $unpaidSalary = (clone $baseQuery)->whereNull('teacher_payout_id')->sum('amount');
        $paidSalary = (clone $baseQuery)->whereNotNull('teacher_payout_id')->sum('amount');

        $salaries = $baseQuery
            ->with(['student', 'course.category', 'schedule.lesson.section', 'payout'])
            ->when($search, fn ($query) => $query->where(fn ($query) => $query
                ->whereHas('student', fn ($student) => $student->where('name', 'like', "%{$search}%")->orWhere('email', 'like', "%{$search}%"))
                ->orWhereHas('course', fn ($course) => $course->where('name', 'like', "%{$search}%")->orWhere('code', 'like', "%{$search}%"))
                ->orWhereHas('schedule.lesson', fn ($lesson) => $lesson->where('title', 'like', "%{$search}%"))))
            ->latest('earned_at')
            ->paginate(10)
            ->withQueryString();

        return view('pages.teacher-salaries.index', compact(
            'salaries', 'search', 'totalSalary', 'monthlySalary', 'totalMeetings', 'unpaidSalary', 'paidSalary'
        ));
    }

    public function proof(Request $request, \App\Models\TeacherPayout $payout): StreamedResponse
    {
        abort_unless($payout->teacher_id === $request->user()->id, 404);
        abort_unless(Storage::exists($payout->transfer_proof_path), 404, 'Bukti transfer tidak ditemukan.');

        return Storage::response(
            $payout->transfer_proof_path,
            'bukti-gaji-PAY-'.str_pad((string) $payout->id, 6, '0', STR_PAD_LEFT).'.'.pathinfo($payout->transfer_proof_path, PATHINFO_EXTENSION),
            ['Content-Disposition' => 'inline']
        );
    }
}
