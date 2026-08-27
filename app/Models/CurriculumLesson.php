<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['curriculum_section_id', 'title', 'meetings', 'sort_order'])]
class CurriculumLesson extends Model
{
    public function section(): BelongsTo
    {
        return $this->belongsTo(CurriculumSection::class, 'curriculum_section_id');
    }
}
