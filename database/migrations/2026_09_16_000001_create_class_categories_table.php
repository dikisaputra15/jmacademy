<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('class_categories', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->unsignedTinyInteger('capacity');
            $table->unsignedInteger('fee_per_meeting')->nullable();
            $table->timestamps();
        });

        foreach (['Reguler A' => 3, 'Reguler B' => 6, 'Private' => 1] as $name => $capacity) {
            DB::table('class_categories')->insert([
                'name' => $name, 'capacity' => $capacity,
                'created_at' => now(), 'updated_at' => now(),
            ]);
        }

        Schema::table('courses', function (Blueprint $table) {
            $table->foreignId('class_category_id')->nullable()->constrained()->restrictOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('courses', function (Blueprint $table) {
            $table->dropConstrainedForeignId('class_category_id');
        });
        Schema::dropIfExists('class_categories');
    }
};
