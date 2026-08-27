<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('phone', 20)->nullable()->after('email');
            $table->string('gender', 10)->nullable()->after('phone');
            $table->date('date_of_birth')->nullable()->after('gender');
            $table->string('province_id', 2)->nullable()->after('date_of_birth');
            $table->string('province_name', 100)->nullable()->after('province_id');
            $table->string('regency_id', 4)->nullable()->after('province_name');
            $table->string('regency_name', 100)->nullable()->after('regency_id');
            $table->string('district_id', 7)->nullable()->after('regency_name');
            $table->string('district_name', 100)->nullable()->after('district_id');
            $table->string('village_id', 10)->nullable()->after('district_name');
            $table->string('village_name', 100)->nullable()->after('village_id');
            $table->string('postal_code', 5)->nullable()->after('village_name');
            $table->text('address')->nullable()->after('postal_code');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'phone', 'gender', 'date_of_birth', 'province_id', 'province_name',
                'regency_id', 'regency_name', 'district_id', 'district_name',
                'village_id', 'village_name', 'postal_code', 'address',
            ]);
        });
    }
};
