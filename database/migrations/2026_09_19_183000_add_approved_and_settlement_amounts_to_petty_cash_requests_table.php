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
            $table->decimal('approved_amount', 15, 2)->nullable()->after('total_amount');
            $table->decimal('settlement_amount', 15, 2)->nullable()->after('approved_amount');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('petty_cash_requests', function (Blueprint $table) {
            $table->dropColumn([
                'approved_amount',
                'settlement_amount',
            ]);
        });
    }
};
