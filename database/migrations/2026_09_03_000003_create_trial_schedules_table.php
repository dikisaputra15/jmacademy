<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('trial_schedules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->unique()->constrained('users')->cascadeOnDelete();
            $table->foreignId('course_id')->constrained()->cascadeOnDelete();
            $table->foreignId('teacher_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('curriculum_lesson_id')->nullable()->constrained()->nullOnDelete();
            $table->date('training_date');
            $table->time('start_time');
            $table->time('end_time');
            $table->string('zoom_url', 1000);
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->constrained('users')->cascadeOnDelete();
            $table->timestamps();
            $table->index(['teacher_id', 'training_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('trial_schedules');
    }
};
