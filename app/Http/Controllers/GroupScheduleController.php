<?php

namespace App\Http\Controllers;

use App\Models\CourseTransaction;
use App\Models\PaidSchedule;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class GroupScheduleController extends Controller
{
    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'transaction_ids' => ['required', 'array', 'min:1', 'max:255'],
            'transaction_ids.*' => ['required', 'integer', 'distinct', 'exists:course_student,id'],
            'teacher_id' => ['required', 'integer', 'exists:users,id'],
            'schedules' => ['required', 'array', 'min:1'],
            'schedules.*.lesson_id' => ['required', 'integer'],
            'schedules.*.meeting_number' => ['required', 'integer', 'min:1'],
            'schedules.*.training_date' => ['required', 'date_format:Y-m-d', 'after_or_equal:today'],
            'schedules.*.start_time' => ['required', 'date_format:H:i'],
            'schedules.*.end_time' => ['required', 'date_format:H:i', 'after:schedules.*.start_time'],
            'schedules.*.zoom_url' => ['required', 'url:http,https', 'max:1000'],
        ]);

        DB::transaction(function () use ($data, $request) {
            // Serialize scheduling for the teacher and all participating students.
            $studentIds = CourseTransaction::whereIn('id', $data['transaction_ids'])->pluck('user_id');
            User::whereIn('id', $studentIds->push($data['teacher_id'])->unique())->orderBy('id')->lockForUpdate()->get();
            $teacher = User::role('guru')->where('is_active', true)->find($data['teacher_id']);
            if (! $teacher) {
                throw ValidationException::withMessages(['teacher_id' => 'Pilih guru yang aktif.']);
            }
            $members = CourseTransaction::with(['classCategory', 'course.sections.lessons'])
                ->whereIn('id', $data['transaction_ids'])->orderBy('id')->lockForUpdate()->get();
            $first = $members->first();
            if (! $first || ! $first->classCategory
                || $members->count() !== $first->classCategory->capacity
                || $members->unique('user_id')->count() !== $members->count()
                || $members->contains(fn ($member) => $member->payment_status !== 'paid'
                    || $member->course_id !== $first->course_id
                    || $member->class_category_id !== $first->class_category_id
                    || $member->paidSchedules()->exists())) {
                throw ValidationException::withMessages(['group' => 'Kelompok harus berisi siswa berbeda, sudah lunas, belum dijadwalkan, dengan course dan kategori sama serta kapasitas penuh.']);
            }
            $expected = collect();
            foreach ($first->course->sections as $section) {
                foreach ($section->lessons as $lesson) {
                    for ($number = 1; $number <= $lesson->meetings; $number++) {
                        $expected->push($lesson->id.'-'.$number);
                    }
                }
            }
            $submitted = collect($data['schedules'])->map(fn ($slot) => $slot['lesson_id'].'-'.$slot['meeting_number']);
            if ($expected->isEmpty() || $submitted->count() !== $expected->count()
                || $submitted->unique()->count() !== $submitted->count()
                || $expected->diff($submitted)->isNotEmpty()) {
                throw ValidationException::withMessages(['schedules' => 'Isi seluruh pertemuan kurikulum tepat satu kali.']);
            }
            foreach ($data['schedules'] as $index => $slot) {
                $conflict = PaidSchedule::whereDate('training_date', $slot['training_date'])
                    ->where('start_time', '<', $slot['end_time'])->where('end_time', '>', $slot['start_time'])
                    ->where(fn ($query) => $query->where('teacher_id', $teacher->id)
                        ->orWhereHas('transaction', fn ($query) => $query->whereIn('user_id', $members->pluck('user_id'))))
                    ->exists();
                if ($conflict) {
                    throw ValidationException::withMessages(["schedules.$index.training_date" => 'Jadwal bentrok dengan jadwal guru atau siswa.']);
                }
                foreach ($members as $member) {
                    PaidSchedule::create([
                        'course_transaction_id' => $member->id,
                        'teacher_id' => $teacher->id,
                        'curriculum_lesson_id' => $slot['lesson_id'],
                        'meeting_number' => $slot['meeting_number'],
                        'training_date' => $slot['training_date'],
                        'start_time' => $slot['start_time'],
                        'end_time' => $slot['end_time'],
                        'zoom_url' => $slot['zoom_url'],
                        'created_by' => $request->user()->id,
                    ]);
                }
            }
        });

        return to_route('student-registrations.index')->with('success', 'Guru dan seluruh jadwal kelompok berhasil ditetapkan untuk setiap siswa.');
    }
}
