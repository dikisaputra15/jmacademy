<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('paid_schedules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('course_transaction_id')->constrained('course_student')->cascadeOnDelete();
            $table->foreignId('teacher_id')->constrained('users')->restrictOnDelete();
            $table->date('training_date');
            $table->time('start_time');
            $table->time('end_time');
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['teacher_id', 'training_date']);
            $table->index(['course_transaction_id', 'training_date']);
            $table->unique(['course_transaction_id', 'training_date', 'start_time'], 'paid_schedule_unique_slot');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('paid_schedules');
    }
};
