<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

#[Fillable([
    'paid_schedule_id', 'teacher_id', 'student_id', 'course_id', 'curriculum_lesson_id',
    'is_present', 'learning_summary', 'notes', 'video_title', 'video_url', 'submitted_at',
])]
class LearningReport extends Model
{
    protected function casts(): array
    {
        return ['is_present' => 'boolean', 'submitted_at' => 'datetime'];
    }

    public function schedule(): BelongsTo { return $this->belongsTo(PaidSchedule::class, 'paid_schedule_id'); }
    public function teacher(): BelongsTo { return $this->belongsTo(User::class, 'teacher_id'); }
    public function student(): BelongsTo { return $this->belongsTo(User::class, 'student_id'); }
    public function course(): BelongsTo { return $this->belongsTo(Course::class); }
    public function lesson(): BelongsTo { return $this->belongsTo(CurriculumLesson::class, 'curriculum_lesson_id'); }
    public function salary(): HasOne { return $this->hasOne(TeacherSalary::class); }
}
