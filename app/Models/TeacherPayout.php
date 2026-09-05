<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'teacher_id', 'paid_by', 'amount', 'bank_name', 'bank_account_number',
    'bank_account_holder', 'transfer_date', 'transfer_proof_path', 'notes',
])]
class TeacherPayout extends Model
{
    protected function casts(): array
    {
        return ['amount' => 'integer', 'transfer_date' => 'date'];
    }

    public function teacher(): BelongsTo { return $this->belongsTo(User::class, 'teacher_id'); }
    public function payer(): BelongsTo { return $this->belongsTo(User::class, 'paid_by'); }
    public function salaries(): HasMany { return $this->hasMany(TeacherSalary::class); }
}
