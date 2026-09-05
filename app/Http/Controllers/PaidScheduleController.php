<?php

namespace App\Http\Controllers;

use App\Models\CourseTransaction;
use App\Models\PaidSchedule;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class PaidScheduleController extends Controller
{
    public function index(Request $request): View
    {
        $search = trim((string) $request->query('search'));

        $paidTransactions = CourseTransaction::query()
            ->with(['student', 'course.teachers', 'course.sections.lessons', 'paidSchedules'])
            ->withCount('paidSchedules')
            ->where('payment_status', 'paid')
            ->latest('verified_at')
            ->get();

        $scheduleOptions = $paidTransactions->mapWithKeys(function (CourseTransaction $transaction) {
            $scheduled = $transaction->paidSchedules
                ->filter(fn (PaidSchedule $schedule) => $schedule->curriculum_lesson_id && $schedule->meeting_number)
                ->mapWithKeys(fn (PaidSchedule $schedule) => [
                    $schedule->curriculum_lesson_id.'-'.$schedule->meeting_number => true,
                ]);
            $slots = collect();

            foreach ($transaction->course->sections as $section) {
                foreach ($section->lessons as $lesson) {
                    for ($meeting = 1; $meeting <= $lesson->meetings; $meeting++) {
                        if (! $scheduled->has($lesson->id.'-'.$meeting)) {
                            $slots->push([
                                'lesson_id' => $lesson->id,
                                'section' => $section->name,
                                'lesson' => $lesson->title,
                                'meeting_number' => $meeting,
                            ]);
                        }
                    }
                }
            }

            return [$transaction->id => [
                'teacher' => $transaction->course->teachers->first()?->only(['id', 'name']),
                'slots' => $slots->values(),
                'total' => $transaction->course->sections->sum(
                    fn ($section) => $section->lessons->sum('meetings')
                ),
                'scheduled' => $transaction->paid_schedules_count,
            ]];
        });

        $schedules = PaidSchedule::query()
            ->with(['transaction.student', 'transaction.course.category', 'teacher', 'lesson.section'])
            ->when($search, function ($query) use ($search) {
                $query->where(function ($query) use ($search) {
                    $query->whereHas('transaction.student', fn ($student) => $student
                        ->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%"))
                        ->orWhereHas('transaction.course', fn ($course) => $course
                            ->where('name', 'like', "%{$search}%")
                            ->orWhere('code', 'like', "%{$search}%"))
                        ->orWhereHas('teacher', fn ($teacher) => $teacher
                            ->where('name', 'like', "%{$search}%"));
                });
            })
            ->orderBy('training_date')
            ->orderBy('start_time')
            ->paginate(15)
            ->withQueryString();

        return view('pages.paid-schedules.index', compact(
            'paidTransactions', 'scheduleOptions', 'schedules', 'search'
        ));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'course_transaction_id' => ['required', 'integer', 'exists:course_student,id'],
            'notes' => ['nullable', 'string', 'max:1000'],
            'schedules' => ['required', 'array', 'min:1', 'max:100'],
            'schedules.*.lesson_id' => ['required', 'integer', 'exists:curriculum_lessons,id'],
            'schedules.*.meeting_number' => ['required', 'integer', 'min:1'],
            'schedules.*.training_date' => ['required', 'date', 'after_or_equal:today'],
            'schedules.*.start_time' => ['required', 'date_format:H:i'],
            'schedules.*.end_time' => ['required', 'date_format:H:i', 'after:schedules.*.start_time'],
            'schedules.*.zoom_url' => ['required', 'url:http,https', 'max:1000'],
        ]);

        $transaction = CourseTransaction::query()
            ->with(['course.teachers', 'course.sections.lessons'])
            ->where('payment_status', 'paid')
            ->findOrFail($validated['course_transaction_id']);

        $teacher = $transaction->course->teachers->first();

        if (! $teacher) {
            throw ValidationException::withMessages([
                'course_transaction_id' => 'Course ini belum memiliki guru yang ditugaskan.',
            ]);
        }

        $lessons = $transaction->course->sections->flatMap->lessons->keyBy('id');

        DB::transaction(function () use ($validated, $transaction, $teacher, $lessons, $request) {
            foreach ($validated['schedules'] as $index => $slot) {
                $lesson = $lessons->get((int) $slot['lesson_id']);

                if (! $lesson || (int) $slot['meeting_number'] > $lesson->meetings) {
                    throw ValidationException::withMessages([
                        "schedules.{$index}.lesson_id" => 'Materi/pertemuan tidak sesuai dengan kurikulum course.',
                    ]);
                }

                $alreadyScheduled = PaidSchedule::query()
                    ->where('course_transaction_id', $transaction->id)
                    ->where('curriculum_lesson_id', $lesson->id)
                    ->where('meeting_number', $slot['meeting_number'])
                    ->exists();

                if ($alreadyScheduled) {
                    throw ValidationException::withMessages([
                        "schedules.{$index}.lesson_id" => 'Pertemuan materi ini sudah memiliki jadwal.',
                    ]);
                }

                $conflict = PaidSchedule::query()
                    ->where('training_date', $slot['training_date'])
                    ->where(function ($query) use ($transaction, $teacher) {
                        $query->where('teacher_id', $teacher->id)
                            ->orWhereHas('transaction', fn ($transactionQuery) => $transactionQuery
                                ->where('user_id', $transaction->user_id));
                    })
                    ->where('start_time', '<', $slot['end_time'])
                    ->where('end_time', '>', $slot['start_time'])
                    ->exists();

                if ($conflict) {
                    throw ValidationException::withMessages([
                        "schedules.{$index}.training_date" => 'Jadwal bentrok dengan jadwal guru atau student pada waktu tersebut.',
                    ]);
                }

                PaidSchedule::create([
                    'course_transaction_id' => $transaction->id,
                    'teacher_id' => $teacher->id,
                    'curriculum_lesson_id' => $lesson->id,
                    'meeting_number' => $slot['meeting_number'],
                    'training_date' => $slot['training_date'],
                    'start_time' => $slot['start_time'],
                    'end_time' => $slot['end_time'],
                    'zoom_url' => $slot['zoom_url'],
                    'notes' => $validated['notes'] ?? null,
                    'created_by' => $request->user()->id,
                ]);
            }
        });

        return to_route('paid-schedules.index')->with('success', count($validated['schedules']).' jadwal training berhasil dibuat.');
    }
}
