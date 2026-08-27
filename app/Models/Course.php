<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['course_category_id', 'name', 'code', 'age_min', 'age_max', 'description', 'is_active', 'sort_order'])]
class Course extends Model
{
    use HasFactory;

    protected function casts(): array
    {
        return ['is_active' => 'boolean'];
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(CourseCategory::class, 'course_category_id');
    }

    public function sections(): HasMany
    {
        return $this->hasMany(CurriculumSection::class)->orderBy('sort_order')->orderBy('id');
    }

    public function teachers(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'course_teacher')->withTimestamps();
    }
}
