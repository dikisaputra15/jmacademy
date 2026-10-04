<?php

namespace Tests\Feature;

use App\Models\ClassCategory;
use App\Models\Course;
use App\Models\CourseCategory;
use App\Models\CurriculumSection;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class ClassCategoryTest extends TestCase
{
    use RefreshDatabase;

    public function test_category_fee_is_configured_for_checkout_without_changing_course_lessons(): void
    {
        Role::findOrCreate('admin');
        Role::findOrCreate('guru');
        Role::findOrCreate('student');
        $admin = User::factory()->create(); $admin->assignRole('admin');
        $student = User::factory()->create(); $student->assignRole('student');
        $classCategory = ClassCategory::where('name', 'Reguler A')->firstOrFail();
        $this->assertSame(3, $classCategory->capacity);
        $this->assertSame(6, ClassCategory::where('name', 'Reguler B')->firstOrFail()->capacity);
        $this->assertSame(1, ClassCategory::where('name', 'Private')->firstOrFail()->capacity);
        $category = CourseCategory::create(['name' => 'Coding', 'slug' => 'coding', 'is_active' => true]);
        $course = Course::create(['course_category_id' => $category->id, 'class_category_id' => $classCategory->id, 'name' => 'Coding A', 'code' => 'CLASS-A', 'is_active' => true]);
        $section = CurriculumSection::create(['course_id' => $course->id, 'name' => 'Dasar']);
        $lesson = $section->lessons()->create(['title' => 'Pertama', 'meetings' => 2, 'fee_per_meeting' => 10000]);

        $this->actingAs($admin)->get(route('courses.edit', $course))->assertOk()->assertDontSee('name="class_category_id"', false);
        $this->actingAs($admin)->get(route('class-categories.index'))->assertOk()->assertSee('Reguler A');
        $this->actingAs($admin)->put(route('class-categories.update', $classCategory), ['fee_per_meeting' => 50000])
            ->assertSessionHasNoErrors()->assertRedirect();
        $this->assertSame(10000, $lesson->fresh()->fee_per_meeting);
        $this->actingAs($admin)->post(route('curriculum.lessons.store', $section), [
            'title' => 'Kedua', 'meetings' => 1, 'sort_order' => 2,
        ])->assertSessionHasNoErrors()->assertRedirect();
        $this->assertDatabaseHas('curriculum_lessons', ['title' => 'Kedua', 'fee_per_meeting' => 0]);
        $this->actingAs($student)->get(route('student-courses.index'))->assertOk()->assertDontSee('Reguler A')->assertSee('Rp 20.000');
        $this->actingAs($student)->get(route('student-courses.payment', $course))->assertOk()->assertSee('Reguler A')->assertSee('data-total="150000"', false);
        $this->actingAs($student)->put(route('class-categories.update', $classCategory), ['fee_per_meeting' => 1])->assertForbidden();
        $this->assertSame(50000, $classCategory->fresh()->fee_per_meeting);
    }
}
