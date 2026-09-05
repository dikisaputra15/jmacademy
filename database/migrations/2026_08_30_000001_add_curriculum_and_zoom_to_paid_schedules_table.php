<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('paid_schedules', function (Blueprint $table) {
            $table->foreignId('curriculum_lesson_id')->nullable()->after('teacher_id')
                ->constrained()->nullOnDelete();
            $table->unsignedSmallInteger('meeting_number')->nullable()->after('curriculum_lesson_id');
            $table->string('zoom_url', 1000)->nullable()->after('end_time');
            $table->unique(
                ['course_transaction_id', 'curriculum_lesson_id', 'meeting_number'],
                'paid_schedule_curriculum_meeting_unique'
            );
        });
    }

    public function down(): void
    {
        Schema::table('paid_schedules', function (Blueprint $table) {
            $table->dropUnique('paid_schedule_curriculum_meeting_unique');
            $table->dropConstrainedForeignId('curriculum_lesson_id');
            $table->dropColumn(['meeting_number', 'zoom_url']);
        });
    }
};
