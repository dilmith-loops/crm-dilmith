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
        Schema::table('quotations', function (Blueprint $table) {
            $table->string('po_applicable')->default('no')->after('third_party_cost');
            $table->string('po_number')->nullable()->after('po_applicable');
            $table->string('po_file_path')->nullable()->after('po_number');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('quotations', function (Blueprint $table) {
            $table->dropColumn(['po_applicable', 'po_number', 'po_file_path']);
        });
    }
};
