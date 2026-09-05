<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('course_student', function (Blueprint $table) {
            $table->foreignId('verified_by')->nullable()->after('payment_status')
                ->constrained('users')->nullOnDelete();
            $table->timestamp('verified_at')->nullable()->after('verified_by');
            $table->text('verification_note')->nullable()->after('verified_at');
            $table->index(['payment_status', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::table('course_student', function (Blueprint $table) {
            $table->dropIndex(['payment_status', 'created_at']);
            $table->dropConstrainedForeignId('verified_by');
            $table->dropColumn(['verified_at', 'verification_note']);
        });
    }
};
