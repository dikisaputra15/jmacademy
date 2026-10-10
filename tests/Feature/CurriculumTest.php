<?php

namespace Tests\Feature;

use App\Models\Course;
use App\Models\CourseCategory;
use App\Models\CurriculumSection;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class CurriculumTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_add_lesson_without_fee_per_meeting(): void
    {
        Role::create(['name' => 'admin']);
        $admin = User::factory()->create(['is_active' => true]);
        $admin->assignRole('admin');

        $category = CourseCategory::create([
            'name' => 'Robotics',
            'slug' => 'robotics',
            'is_active' => true,
        ]);
        $course = Course::create([
            'course_category_id' => $category->id,
            'name' => 'Game Level 2',
            'code' => 'GI-01',
            'is_active' => true,
        ]);
        $section = CurriculumSection::create([
            'course_id' => $course->id,
            'name' => 'Fundamental',
        ]);

        $this->actingAs($admin)
            ->post(route('curriculum.lessons.store', $section), [
                'title' => 'Game Logic',
                'meetings' => 2,
                'sort_order' => 1,
            ])
            ->assertRedirect()
            ->assertSessionHasNoErrors();

        $this->assertDatabaseHas('curriculum_lessons', [
            'curriculum_section_id' => $section->id,
            'title' => 'Game Logic',
            'meetings' => 2,
            'fee_per_meeting' => 0,
        ]);

        $this->actingAs($admin)
            ->get(route('courses.curriculum', $course))
            ->assertOk()
            ->assertSee('Game Logic')
            ->assertDontSee('name="fee_per_meeting"', false)
            ->assertDontSee('Rp ');

        $lesson = $section->lessons()->firstOrFail();
        $resources = '[Project](https://scratch.mit.edu/projects/123)'."\n\n".'[Modul](https://example.com/modul)';
        $this->put(route('curriculum.lessons.resources', $lesson), ['resources' => $resources])
            ->assertSessionHasNoErrors();
        $this->assertSame($resources, $lesson->fresh()->resources);
        $rendered = \Illuminate\Support\Facades\Blade::render('<x-lesson-resources :lesson="$lesson" />', ['lesson' => $lesson->fresh()]);
        $this->assertStringContainsString('href="https://scratch.mit.edu/projects/123"', $rendered);
        $lesson->resources = '<script>alert(1)</script> [bad](javascript:alert(1))';
        $rendered = \Illuminate\Support\Facades\Blade::render('<x-lesson-resources :lesson="$lesson" />', ['lesson' => $lesson]);
        $this->assertStringNotContainsString('<script>', $rendered);
        $this->assertStringNotContainsString('href="javascript:', $rendered);
        $this->put(route('curriculum.lessons.resources', $lesson), ['resources' => null])->assertSessionHasNoErrors();
        $this->assertNull($lesson->fresh()->resources);
        Role::findOrCreate('student');
        $student = User::factory()->create();
        $student->assignRole('student');
        $this->actingAs($student)->put(route('curriculum.lessons.resources', $lesson), ['resources' => 'changed'])->assertForbidden();

    }

    public function test_visual_resources_preserve_formatting_and_remove_unsafe_html(): void
    {
        $html = \App\Support\LessonResources::render('<p style="text-align:center" onclick="alert(1)"><strong>Modul</strong> <a href="https://example.com/modul">Buka</a><a href="javascript:alert(1)">Bad</a><img src="x" onerror="alert(1)"><script>alert(1)</script></p>');
        $this->assertStringContainsString('<strong>Modul</strong>', $html);
        $this->assertStringContainsString('text-align:center', $html);
        $this->assertStringContainsString('href="https://example.com/modul"', $html);
        $this->assertStringNotContainsString('javascript:', $html);
        $this->assertStringNotContainsString('onclick', $html);
        $this->assertStringNotContainsString('<script', $html);
        $this->assertStringNotContainsString('<img', $html);
    }

    public function test_lesson_creation_ignores_submitted_fee_per_meeting(): void
    {
        Role::create(['name' => 'admin']);
        $admin = User::factory()->create(['is_active' => true]);
        $admin->assignRole('admin');

        $category = CourseCategory::create([
            'name' => 'Robotics',
            'slug' => 'robotics',
            'is_active' => true,
        ]);
        $course = Course::create([
            'course_category_id' => $category->id,
            'name' => 'Game Level 2',
            'code' => 'GI-01',
            'is_active' => true,
        ]);
        $section = CurriculumSection::create([
            'course_id' => $course->id,
            'name' => 'Fundamental',
        ]);

        $this->actingAs($admin)
            ->from(route('courses.curriculum', $course))
            ->post(route('curriculum.lessons.store', $section), [
                'title' => 'Game Logic',
                'meetings' => 1,
                'fee_per_meeting' => -1,
                'sort_order' => 1,
            ])
            ->assertRedirect(route('courses.curriculum', $course))
            ->assertSessionHasNoErrors();

        $this->assertDatabaseHas('curriculum_lessons', [
            'curriculum_section_id' => $section->id,
            'title' => 'Game Logic',
            'fee_per_meeting' => 0,
        ]);
    }
}
