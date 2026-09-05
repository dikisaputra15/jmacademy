<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('learning_reports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('paid_schedule_id')->unique()->constrained()->cascadeOnDelete();
            $table->foreignId('teacher_id')->constrained('users')->restrictOnDelete();
            $table->foreignId('student_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('course_id')->constrained()->cascadeOnDelete();
            $table->foreignId('curriculum_lesson_id')->nullable()->constrained()->nullOnDelete();
            $table->boolean('is_present')->default(true);
            $table->text('learning_summary');
            $table->text('notes')->nullable();
            $table->string('video_title')->nullable();
            $table->string('video_url', 1000)->nullable();
            $table->timestamp('submitted_at');
            $table->timestamps();
            $table->index(['teacher_id', 'submitted_at']);
            $table->index(['student_id', 'submitted_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('learning_reports');
    }
};
