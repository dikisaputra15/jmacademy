<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'course_transaction_id', 'teacher_id', 'student_id', 'course_id', 'grade',
    'understanding', 'logic', 'creativity', 'strengths', 'improvements',
    'recommendation', 'submitted_at',
])]
class ParentReport extends Model
{
    protected function casts(): array
    {
        return [
            'understanding' => 'boolean', 'logic' => 'boolean', 'creativity' => 'boolean',
            'submitted_at' => 'datetime',
        ];
    }

    public function transaction(): BelongsTo { return $this->belongsTo(CourseTransaction::class, 'course_transaction_id'); }
    public function teacher(): BelongsTo { return $this->belongsTo(User::class, 'teacher_id'); }
    public function student(): BelongsTo { return $this->belongsTo(User::class, 'student_id'); }
    public function course(): BelongsTo { return $this->belongsTo(Course::class); }
}
