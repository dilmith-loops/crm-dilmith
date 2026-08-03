<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('deals', function (Blueprint $table) {
            $table->string('pipeline')->nullable()->after('stage');
            $table->string('currency', 3)->default('LKR')->after('amount');
            $table->foreignId('user_id')->nullable()->after('customer_id')->constrained()->nullOnDelete(); // Deal Owner
            $table->string('type')->nullable()->after('pipeline'); // New/Existing Business
            $table->string('priority')->nullable()->after('type'); // Low, Medium, High
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('deals', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->dropColumn(['pipeline', 'currency', 'user_id', 'type', 'priority']);
        });
    }
};
