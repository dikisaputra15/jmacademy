<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['name', 'capacity', 'fee_per_meeting'])]
class ClassCategory extends Model
{
    protected function casts(): array
    {
        return ['capacity' => 'integer', 'fee_per_meeting' => 'integer'];
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(CourseTransaction::class);
    }

    public function courses(): HasMany
    {
        return $this->hasMany(Course::class);
    }
}
