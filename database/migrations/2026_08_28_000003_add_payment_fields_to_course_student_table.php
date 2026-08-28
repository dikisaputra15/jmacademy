<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('course_student', function (Blueprint $table) {
            $table->unsignedBigInteger('amount')->default(0)->after('user_id');
            $table->string('sender_name')->nullable()->after('amount');
            $table->string('sender_bank', 100)->nullable()->after('sender_name');
            $table->date('transfer_date')->nullable()->after('sender_bank');
            $table->string('payment_proof_path')->nullable()->after('transfer_date');
            $table->string('payment_status', 20)->default('pending')->after('payment_proof_path');
        });
    }

    public function down(): void
    {
        Schema::table('course_student', function (Blueprint $table) {
            $table->dropColumn(['amount', 'sender_name', 'sender_bank', 'transfer_date', 'payment_proof_path', 'payment_status']);
        });
    }
};
