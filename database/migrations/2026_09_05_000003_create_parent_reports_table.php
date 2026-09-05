<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('parent_reports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('course_transaction_id')->unique()->constrained('course_student')->cascadeOnDelete();
            $table->foreignId('teacher_id')->constrained('users')->restrictOnDelete();
            $table->foreignId('student_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('course_id')->constrained()->cascadeOnDelete();
            $table->string('grade', 2);
            $table->boolean('understanding')->default(false);
            $table->boolean('logic')->default(false);
            $table->boolean('creativity')->default(false);
            $table->text('strengths');
            $table->text('improvements')->nullable();
            $table->text('recommendation')->nullable();
            $table->timestamp('submitted_at');
            $table->timestamps();
            $table->index(['teacher_id', 'submitted_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('parent_reports');
    }
};
