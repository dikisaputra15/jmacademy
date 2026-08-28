<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'course_id', 'user_id', 'amount', 'sender_name', 'sender_bank',
    'transfer_date', 'payment_proof_path', 'payment_status',
])]
class CourseTransaction extends Model
{
    protected $table = 'course_student';

    protected function casts(): array
    {
        return [
            'amount' => 'integer',
            'transfer_date' => 'date',
        ];
    }

    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
