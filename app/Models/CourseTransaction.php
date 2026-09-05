<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

#[Fillable([
    'course_id', 'user_id', 'amount', 'sender_name', 'sender_bank',
    'transfer_date', 'payment_proof_path', 'payment_status',
    'verified_by', 'verified_at', 'verification_note',
])]
class CourseTransaction extends Model
{
    protected $table = 'course_student';

    protected function casts(): array
    {
        return [
            'amount' => 'integer',
            'transfer_date' => 'date',
            'verified_at' => 'datetime',
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

    public function verifier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verified_by');
    }

    public function paidSchedules(): HasMany
    {
        return $this->hasMany(PaidSchedule::class, 'course_transaction_id')
            ->orderBy('training_date')->orderBy('start_time');
    }

    public function parentReport(): HasOne
    {
        return $this->hasOne(ParentReport::class, 'course_transaction_id');
    }
}
