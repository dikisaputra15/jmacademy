<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('teacher_salaries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('learning_report_id')->unique()->constrained()->cascadeOnDelete();
            $table->foreignId('teacher_id')->constrained('users')->restrictOnDelete();
            $table->foreignId('student_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('course_id')->constrained()->cascadeOnDelete();
            $table->foreignId('paid_schedule_id')->unique()->constrained()->cascadeOnDelete();
            $table->unsignedBigInteger('amount')->default(0);
            $table->timestamp('earned_at');
            $table->timestamps();
            $table->index(['teacher_id', 'earned_at']);
        });

        DB::table('learning_reports')
            ->leftJoin('curriculum_lessons', 'curriculum_lessons.id', '=', 'learning_reports.curriculum_lesson_id')
            ->select([
                'learning_reports.id as learning_report_id', 'learning_reports.teacher_id',
                'learning_reports.student_id', 'learning_reports.course_id',
                'learning_reports.paid_schedule_id', 'learning_reports.submitted_at',
                'learning_reports.created_at', 'learning_reports.updated_at',
                'curriculum_lessons.fee_per_meeting',
            ])
            ->orderBy('learning_reports.id')
            ->each(function ($report) {
                DB::table('teacher_salaries')->insert([
                    'learning_report_id' => $report->learning_report_id,
                    'teacher_id' => $report->teacher_id,
                    'student_id' => $report->student_id,
                    'course_id' => $report->course_id,
                    'paid_schedule_id' => $report->paid_schedule_id,
                    'amount' => $report->fee_per_meeting ?? 0,
                    'earned_at' => $report->submitted_at,
                    'created_at' => $report->created_at,
                    'updated_at' => $report->updated_at,
                ]);
            });
    }

    public function down(): void
    {
        Schema::dropIfExists('teacher_salaries');
    }
};
