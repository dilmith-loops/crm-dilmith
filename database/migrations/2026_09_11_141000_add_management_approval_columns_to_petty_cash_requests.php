<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('petty_cash_requests', function (Blueprint $table) {
            $table->timestamp('management_approved_at')->nullable()->after('sent_to_management_by');
            $table->foreignId('management_approved_by')->nullable()->after('management_approved_at')->constrained('users')->onDelete('set null');
            $table->text('management_rejection_note')->nullable()->after('admin_rejection_note');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('petty_cash_requests', function (Blueprint $table) {
            $table->dropForeign(['management_approved_by']);
            $table->dropColumn([
                'management_approved_at',
                'management_approved_by',
                'management_rejection_note',
            ]);
        });
    }
};
