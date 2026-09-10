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
            $table->text('management_notes')->nullable()->after('extra_notes');
            $table->timestamp('sent_to_management_at')->nullable()->after('management_notes');
            $table->foreignId('sent_to_management_by')->nullable()->after('sent_to_management_at')->constrained('users')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('petty_cash_requests', function (Blueprint $table) {
            $table->dropForeign(['sent_to_management_by']);
            $table->dropColumn([
                'management_notes',
                'sent_to_management_at',
                'sent_to_management_by',
            ]);
        });
    }
};
