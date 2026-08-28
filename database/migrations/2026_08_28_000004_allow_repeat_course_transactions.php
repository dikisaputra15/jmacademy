<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('course_student', function (Blueprint $table) {
            $table->index('course_id');
            $table->index(['user_id', 'created_at']);
        });

        Schema::table('course_student', function (Blueprint $table) {
            $table->dropUnique(['course_id', 'user_id']);
        });
    }

    public function down(): void
    {
        Schema::table('course_student', function (Blueprint $table) {
            $table->unique(['course_id', 'user_id']);
        });

        Schema::table('course_student', function (Blueprint $table) {
            $table->dropIndex(['user_id', 'created_at']);
            $table->dropIndex(['course_id']);
        });
    }
};
