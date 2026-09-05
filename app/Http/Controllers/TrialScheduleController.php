<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\PaidSchedule;
use App\Models\TrialSchedule;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class TrialScheduleController extends Controller
{
    public function index(Request $request): View
    {
        $search = trim((string) $request->query('search'));
        $usedStudentIds = TrialSchedule::query()->pluck('student_id');
        $students = User::role('student')->whereNotIn('id', $usedStudentIds)->orderBy('name')->get(['id', 'name', 'email']);
        $courses = Course::query()->with(['teachers:id,name', 'sections.lessons'])->where('is_active', true)->orderBy('name')->get();
        $courseOptions = $courses->mapWithKeys(fn (Course $course) => [$course->id => [
            'teacher' => $course->teachers->first()?->only(['id', 'name']),
            'lessons' => $course->sections->flatMap(fn ($section) => $section->lessons->map(fn ($lesson) => [
                'id' => $lesson->id, 'name' => $section->name.' · '.$lesson->title,
            ]))->values(),
        ]]);
        $schedules = TrialSchedule::query()
            ->with(['student', 'course.category', 'teacher', 'lesson.section'])
            ->when($search, fn ($query) => $query->where(fn ($query) => $query
                ->whereHas('student', fn ($student) => $student->where('name', 'like', "%{$search}%")->orWhere('email', 'like', "%{$search}%"))
                ->orWhereHas('course', fn ($course) => $course->where('name', 'like', "%{$search}%")->orWhere('code', 'like', "%{$search}%"))
                ->orWhereHas('teacher', fn ($teacher) => $teacher->where('name', 'like', "%{$search}%"))))
            ->orderBy('training_date')->orderBy('start_time')->paginate(15)->withQueryString();

        return view('pages.trial-schedules.index', compact('students', 'courses', 'courseOptions', 'schedules', 'search'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'student_id' => ['required', 'integer', 'exists:users,id'],
            'course_id' => ['required', 'integer', 'exists:courses,id'],
            'curriculum_lesson_id' => ['nullable', 'integer', 'exists:curriculum_lessons,id'],
            'training_date' => ['required', 'date', 'after_or_equal:today'],
            'start_time' => ['required', 'date_format:H:i'],
            'end_time' => ['required', 'date_format:H:i', 'after:start_time'],
            'zoom_url' => ['required', 'url:http,https', 'max:1000'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        $student = User::role('student')->findOrFail($validated['student_id']);
        $course = Course::with(['teachers', 'sections.lessons'])->where('is_active', true)->findOrFail($validated['course_id']);
        $teacher = $course->teachers->first();
        if (! $teacher) throw ValidationException::withMessages(['course_id' => 'Course belum memiliki guru pengajar.']);
        if (TrialSchedule::where('student_id', $student->id)->exists()) throw ValidationException::withMessages(['student_id' => 'Student ini sudah pernah mendapatkan trial. Setiap student hanya dapat trial satu kali.']);
        if (! empty($validated['curriculum_lesson_id']) && ! $course->sections->flatMap->lessons->contains('id', $validated['curriculum_lesson_id'])) {
            throw ValidationException::withMessages(['curriculum_lesson_id' => 'Materi tidak sesuai dengan course yang dipilih.']);
        }

        $conflictsPaid = PaidSchedule::where('teacher_id', $teacher->id)->whereDate('training_date', $validated['training_date'])->where('start_time', '<', $validated['end_time'])->where('end_time', '>', $validated['start_time'])->exists();
        $conflictsTrial = TrialSchedule::whereDate('training_date', $validated['training_date'])->where(fn ($query) => $query->where('teacher_id', $teacher->id)->orWhere('student_id', $student->id))->where('start_time', '<', $validated['end_time'])->where('end_time', '>', $validated['start_time'])->exists();
        if ($conflictsPaid || $conflictsTrial) throw ValidationException::withMessages(['training_date' => 'Jadwal bentrok dengan jadwal guru atau student pada waktu tersebut.']);

        DB::transaction(fn () => TrialSchedule::create([...$validated, 'teacher_id' => $teacher->id, 'created_by' => $request->user()->id]));

        return to_route('trial-schedules.index')->with('success', 'Jadwal trial berhasil dibuat. Student tidak dapat dijadwalkan trial kembali.');
    }
}
