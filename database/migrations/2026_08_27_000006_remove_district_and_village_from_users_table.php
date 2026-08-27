<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['district_id', 'district_name', 'village_id', 'village_name']);
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('district_id', 7)->nullable()->after('regency_name');
            $table->string('district_name', 100)->nullable()->after('district_id');
            $table->string('village_id', 10)->nullable()->after('district_name');
            $table->string('village_name', 100)->nullable()->after('village_id');
        });
    }
};
