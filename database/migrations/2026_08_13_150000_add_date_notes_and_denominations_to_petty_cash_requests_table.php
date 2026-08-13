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
            $table->timestamp('issued_at')->nullable()->after('is_iou');
            $table->json('issued_money_notes')->nullable()->after('issued_at');
            $table->text('settlement_note')->nullable()->after('settled_at');
            $table->json('settlement_money_notes')->nullable()->after('settlement_note');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('petty_cash_requests', function (Blueprint $table) {
            $table->dropColumn([
                'issued_at',
                'issued_money_notes',
                'settlement_note',
                'settlement_money_notes',
            ]);
        });
    }
};
