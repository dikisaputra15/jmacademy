<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('curriculum_lessons', function (Blueprint $table) {
            $table->unsignedBigInteger('fee_per_meeting')->default(0)->after('meetings');
        });
    }

    public function down(): void
    {
        Schema::table('curriculum_lessons', function (Blueprint $table) {
            $table->dropColumn('fee_per_meeting');
        });
    }
};
