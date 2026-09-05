<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('teacher_payouts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('teacher_id')->constrained('users')->restrictOnDelete();
            $table->foreignId('paid_by')->nullable()->constrained('users')->nullOnDelete();
            $table->unsignedBigInteger('amount');
            $table->string('bank_name', 100);
            $table->string('bank_account_number', 100);
            $table->string('bank_account_holder');
            $table->date('transfer_date');
            $table->string('transfer_proof_path');
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->index(['teacher_id', 'transfer_date']);
        });

        Schema::table('teacher_salaries', function (Blueprint $table) {
            $table->foreignId('teacher_payout_id')->nullable()->after('paid_schedule_id')
                ->constrained('teacher_payouts')->nullOnDelete();
            $table->index(['teacher_id', 'teacher_payout_id']);
        });
    }

    public function down(): void
    {
        Schema::table('teacher_salaries', function (Blueprint $table) {
            $table->dropForeign(['teacher_payout_id']);
            $table->dropIndex(['teacher_id', 'teacher_payout_id']);
            $table->dropColumn('teacher_payout_id');
        });
        Schema::dropIfExists('teacher_payouts');
    }
};
