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
        Schema::table('quotations', function (Blueprint $table) {
            $table->string('attention_to')->nullable();
            $table->string('address_line_1')->nullable();
            $table->string('address_line_2')->nullable();
            $table->string('address_line_3')->nullable();
            $table->string('designation')->nullable();
            $table->string('currency')->default('LKR');
            $table->string('heading')->nullable();
            $table->text('terms')->nullable();
            $table->text('special_terms')->nullable();
            $table->string('advance_payment')->nullable();
            $table->decimal('advance_percentage', 5, 2)->nullable();
            $table->string('senior_manager')->nullable();
            $table->text('additional_notes')->nullable();
            $table->boolean('sscl_applicable')->default(false);
            $table->boolean('vat_applicable')->default(false);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('quotations', function (Blueprint $table) {
            //
        });
    }
};
