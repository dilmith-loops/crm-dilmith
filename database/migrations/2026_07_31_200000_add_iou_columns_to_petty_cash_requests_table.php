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
            $table->boolean('is_iou')->default(false)->after('total_amount');
            $table->string('settlement_signature_path')->nullable()->after('signature_path');
            $table->timestamp('settled_at')->nullable()->after('settlement_signature_path');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('petty_cash_requests', function (Blueprint $table) {
            $table->dropColumn(['is_iou', 'settlement_signature_path', 'settled_at']);
        });
    }
};
