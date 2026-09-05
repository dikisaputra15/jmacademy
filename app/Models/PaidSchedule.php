<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

#[Fillable([
    'course_transaction_id', 'teacher_id', 'curriculum_lesson_id', 'meeting_number',
    'training_date', 'start_time', 'end_time', 'zoom_url', 'notes', 'created_by',
])]
class PaidSchedule extends Model
{
    protected function casts(): array
    {
        return ['training_date' => 'date'];
    }

    public function transaction(): BelongsTo
    {
        return $this->belongsTo(CourseTransaction::class, 'course_transaction_id');
    }

    public function teacher(): BelongsTo
    {
        return $this->belongsTo(User::class, 'teacher_id');
    }

    public function lesson(): BelongsTo
    {
        return $this->belongsTo(CurriculumLesson::class, 'curriculum_lesson_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function learningReport(): HasOne
    {
        return $this->hasOne(LearningReport::class);
    }

    public function hasEnded(): bool
    {
        return $this->training_date->isBefore(today())
            || ($this->training_date->isToday() && $this->end_time <= now()->format('H:i:s'));
    }

    public function canJoinZoom(): bool
    {
        return (bool) $this->zoom_url
            && ! $this->training_date->isAfter(today())
            && ! $this->hasEnded();
    }
}
