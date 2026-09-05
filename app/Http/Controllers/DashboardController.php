<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\CourseTransaction;
use App\Models\PaidSchedule;
use App\Models\TrialSchedule;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();

        if ($user->hasRole('student')) {
            $studentNearestSchedules = PaidSchedule::query()
                ->with(['transaction.course', 'teacher', 'lesson.section'])
                ->whereHas('transaction', fn ($query) => $query
                    ->where('user_id', $user->id)
                    ->where('payment_status', 'paid'))
                ->whereDate('training_date', '>=', today())
                ->orderBy('training_date')
                ->orderBy('start_time')
                ->limit(4)
                ->get();

            return view('pages.dashboard', compact('studentNearestSchedules'));
        }

        if ($user->hasRole('admin')) {
            $teacherCount = User::role('guru')->count();
            $adminStudentCount = User::role('student')->count();
            $activeCourseCount = Course::query()->where('is_active', true)->count();
            $successfulTransactionCount = CourseTransaction::query()->where('payment_status', 'paid')->count();
            $weekStart = today()->startOfWeek();
            $requestedWeek = (string) $request->query('week');

            if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $requestedWeek)) {
                try {
                    $parsedWeek = Carbon::createFromFormat('Y-m-d', $requestedWeek);
                    if ($parsedWeek->format('Y-m-d') === $requestedWeek) $weekStart = $parsedWeek->startOfWeek();
                } catch (\Throwable) {
                    // Gunakan minggu ini jika parameter tidak valid.
                }
            }

            $weekEnd = $weekStart->copy()->endOfWeek();
            $adminCalendarDays = collect(range(0, 6))->map(fn (int $day) => $weekStart->copy()->addDays($day));
            $adminTimeSlots = collect(range(0, 47))->map(fn (int $slot) => sprintf('%02d:%02d', intdiv($slot * 30, 60), ($slot * 30) % 60));
            $paidSchedules = PaidSchedule::query()
                ->with(['teacher:id,name', 'transaction.course:id,name,code', 'transaction.student:id,name'])
                ->whereBetween('training_date', [$weekStart->toDateString(), $weekEnd->toDateString()])
                ->get()->map(fn (PaidSchedule $schedule) => [
                    'type' => 'paid', 'date' => $schedule->training_date,
                    'start_time' => substr($schedule->start_time, 0, 5), 'end_time' => substr($schedule->end_time, 0, 5),
                    'teacher' => $schedule->teacher?->name ?? 'Guru', 'course' => $schedule->transaction?->course?->name ?? 'Course',
                    'student' => $schedule->transaction?->student?->name ?? 'Student',
                ]);
            $trialSchedules = TrialSchedule::query()
                ->with(['teacher:id,name', 'course:id,name,code', 'student:id,name'])
                ->whereBetween('training_date', [$weekStart->toDateString(), $weekEnd->toDateString()])
                ->get()->map(fn (TrialSchedule $schedule) => [
                    'type' => 'trial', 'date' => $schedule->training_date,
                    'start_time' => substr($schedule->start_time, 0, 5), 'end_time' => substr($schedule->end_time, 0, 5),
                    'teacher' => $schedule->teacher?->name ?? 'Guru', 'course' => $schedule->course?->name ?? 'Course',
                    'student' => $schedule->student?->name ?? 'Student',
                ]);
            $adminCalendarSchedules = $paidSchedules->concat($trialSchedules)
                ->sortBy(fn (array $schedule) => $schedule['date']->format('Y-m-d').' '.$schedule['start_time'])->values();

            return view('pages.dashboard', compact(
                'teacherCount', 'adminStudentCount', 'activeCourseCount', 'successfulTransactionCount',
                'weekStart', 'weekEnd', 'adminCalendarDays', 'adminTimeSlots', 'adminCalendarSchedules'
            ));
        }

        if (! $user->hasRole('guru')) {
            return view('pages.dashboard');
        }

        $teacherId = $user->id;
        $studentCount = CourseTransaction::query()
            ->where('payment_status', 'paid')
            ->whereHas('course.teachers', fn ($query) => $query->where('users.id', $teacherId))
            ->distinct('user_id')
            ->count('user_id');
        $courseCount = Course::query()
            ->whereHas('teachers', fn ($query) => $query->where('users.id', $teacherId))
            ->count();
        $upcomingCount = PaidSchedule::query()
            ->where('teacher_id', $teacherId)
            ->whereDate('training_date', '>=', today())
            ->count();
        $nearestSchedules = PaidSchedule::query()
            ->with(['transaction.student', 'transaction.course', 'lesson.section'])
            ->where('teacher_id', $teacherId)
            ->whereDate('training_date', '>=', today())
            ->orderBy('training_date')
            ->orderBy('start_time')
            ->limit(6)
            ->get();

        return view('pages.dashboard', compact(
            'studentCount', 'courseCount', 'upcomingCount', 'nearestSchedules'
        ));
    }
}
