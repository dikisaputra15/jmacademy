<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'learning_report_id', 'teacher_id', 'student_id', 'course_id',
    'paid_schedule_id', 'teacher_payout_id', 'amount', 'earned_at',
])]
class TeacherSalary extends Model
{
    protected function casts(): array
    {
        return ['amount' => 'integer', 'earned_at' => 'datetime'];
    }

    public function report(): BelongsTo { return $this->belongsTo(LearningReport::class, 'learning_report_id'); }
    public function teacher(): BelongsTo { return $this->belongsTo(User::class, 'teacher_id'); }
    public function student(): BelongsTo { return $this->belongsTo(User::class, 'student_id'); }
    public function course(): BelongsTo { return $this->belongsTo(Course::class); }
    public function schedule(): BelongsTo { return $this->belongsTo(PaidSchedule::class, 'paid_schedule_id'); }
    public function payout(): BelongsTo { return $this->belongsTo(TeacherPayout::class, 'teacher_payout_id'); }
}
