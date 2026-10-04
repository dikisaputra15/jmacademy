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
        $this->actingAs($admin)->put(route('class-categories.update', $classCategory), ['name' => $classCategory->name, 'capacity' => $classCategory->capacity, 'fee_per_meeting' => 50000])
            ->assertSessionHasNoErrors()->assertRedirect();
        $this->assertSame(10000, $lesson->fresh()->fee_per_meeting);
        $this->actingAs($admin)->post(route('curriculum.lessons.store', $section), [
            'title' => 'Kedua', 'meetings' => 1, 'sort_order' => 2,
        ])->assertSessionHasNoErrors()->assertRedirect();
        $this->assertDatabaseHas('curriculum_lessons', ['title' => 'Kedua', 'fee_per_meeting' => 0]);
        $this->actingAs($student)->get(route('student-courses.index'))->assertOk()->assertDontSee('Reguler A')->assertDontSee('Rp 50.000')->assertDontSee('Rp 20.000');
        $this->actingAs($student)->get(route('student-courses.payment', $course))->assertOk()->assertSee('Reguler A')->assertSee('data-total="50000"', false);
        $this->actingAs($student)->put(route('class-categories.update', $classCategory), ['fee_per_meeting' => 1])->assertForbidden();
        $this->assertSame(50000, $classCategory->fresh()->fee_per_meeting);
    }
    public function test_admin_can_create_update_and_delete_unused_categories(): void
    {
        Role::findOrCreate('admin');
        $admin = User::factory()->create();
        $admin->assignRole('admin');
        $this->actingAs($admin)->post(route('class-categories.store'), [
            'name' => 'Workshop', 'capacity' => 12, 'fee_per_meeting' => 80000,
        ])->assertSessionHasNoErrors()->assertRedirect(route('class-categories.index'));
        $category = ClassCategory::where('name', 'Workshop')->firstOrFail();
        $this->put(route('class-categories.update', $category), [
            'name' => 'Workshop Plus', 'capacity' => 15, 'fee_per_meeting' => 90000,
        ])->assertSessionHasNoErrors();
        $this->assertDatabaseHas('class_categories', ['id' => $category->id, 'name' => 'Workshop Plus', 'capacity' => 15, 'fee_per_meeting' => 90000]);
        $this->delete(route('class-categories.destroy', $category))->assertSessionHasNoErrors();
        $this->assertDatabaseMissing('class_categories', ['id' => $category->id]);
    }

    public function test_category_validation_and_student_access(): void
    {
        Role::findOrCreate('admin');
        Role::findOrCreate('student');
        $admin = User::factory()->create();
        $admin->assignRole('admin');
        $category = ClassCategory::firstOrFail();
        $this->actingAs($admin)->post(route('class-categories.store'), [
            'name' => $category->name, 'capacity' => 256, 'fee_per_meeting' => -1,
        ])->assertSessionHasErrors(['name', 'capacity', 'fee_per_meeting']);
        $student = User::factory()->create();
        $student->assignRole('student');
        $this->actingAs($student)->post(route('class-categories.store'), [])->assertForbidden();
        $this->delete(route('class-categories.destroy', $category))->assertForbidden();
    }

    public function test_categories_used_by_transactions_cannot_be_deleted(): void
    {
        Role::findOrCreate('admin');
        $admin = User::factory()->create();
        $admin->assignRole('admin');
        $category = ClassCategory::firstOrFail();
        $courseCategory = CourseCategory::create(['name' => 'Coding', 'slug' => 'coding', 'is_active' => true]);
        $course = Course::create(['course_category_id' => $courseCategory->id, 'name' => 'Coding', 'code' => 'TEST', 'is_active' => true]);
        \App\Models\CourseTransaction::create(['course_id' => $course->id, 'user_id' => $admin->id, 'class_category_id' => $category->id, 'amount' => 50000]);
        $this->actingAs($admin)->delete(route('class-categories.destroy', $category))->assertSessionHasErrors('category');
        $this->assertDatabaseHas('class_categories', ['id' => $category->id]);
    }
}
