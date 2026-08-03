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
        Schema::table('quotation_items', function (Blueprint $table) {
            $table->decimal('unit_price', 15, 4)->change();
            $table->decimal('amount', 15, 4)->change();
            $table->decimal('sscl_amount', 15, 4)->change();
            $table->decimal('vat_amount', 15, 4)->change();
            $table->decimal('total_with_vat', 15, 4)->change();
        });

        Schema::table('invoice_items', function (Blueprint $table) {
            $table->decimal('unit_price', 15, 4)->change();
            $table->decimal('amount', 15, 4)->change();
            $table->decimal('sscl_amount', 15, 4)->change();
            $table->decimal('vat_amount', 15, 4)->change();
            $table->decimal('total_with_vat', 15, 4)->change();
        });
        
        Schema::table('quotations', function (Blueprint $table) {
            $table->decimal('total_amount', 15, 4)->change();
        });

        Schema::table('invoices', function (Blueprint $table) {
            $table->decimal('total_amount', 15, 4)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('quotation_items', function (Blueprint $table) {
            $table->decimal('unit_price', 15, 2)->change();
            $table->decimal('amount', 15, 2)->change();
            $table->decimal('sscl_amount', 15, 2)->change();
            $table->decimal('vat_amount', 15, 2)->change();
            $table->decimal('total_with_vat', 15, 2)->change();
        });

        Schema::table('invoice_items', function (Blueprint $table) {
            $table->decimal('unit_price', 15, 2)->change();
            $table->decimal('amount', 15, 2)->change();
            $table->decimal('sscl_amount', 15, 2)->change();
            $table->decimal('vat_amount', 15, 2)->change();
            $table->decimal('total_with_vat', 15, 2)->change();
        });
        
        Schema::table('quotations', function (Blueprint $table) {
            $table->decimal('total_amount', 15, 2)->change();
        });

        Schema::table('invoices', function (Blueprint $table) {
            $table->decimal('total_amount', 15, 2)->change();
        });
    }
};
