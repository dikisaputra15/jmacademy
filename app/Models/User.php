<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

#[Fillable([
    'name', 'email', 'password', 'is_active', 'phone', 'gender', 'date_of_birth',
    'province_id', 'province_name', 'regency_id', 'regency_name', 'postal_code', 'address',
    'parent_name', 'parent_phone',
])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable, HasRoles;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
            'date_of_birth' => 'date',
        ];
    }

    public function teachingCourses(): BelongsToMany
    {
        return $this->belongsToMany(Course::class, 'course_teacher')->withTimestamps();
    }

    public function enrolledCourses(): BelongsToMany
    {
        return $this->belongsToMany(Course::class, 'course_student')
            ->withPivot(['amount', 'sender_name', 'sender_bank', 'transfer_date', 'payment_proof_path', 'payment_status'])
            ->withTimestamps();
    }

    public function courseTransactions(): HasMany
    {
        return $this->hasMany(CourseTransaction::class);
    }
}
