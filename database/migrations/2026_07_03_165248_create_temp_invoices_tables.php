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
        Schema::create('temp_invoices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('quotation_id')->nullable()->constrained('quotations')->onDelete('set null');
            $table->foreignId('customer_id')->constrained()->onDelete('cascade');
            $table->string('temp_invoice_number');
            $table->date('date');
            $table->date('due_date');
            $table->decimal('total_amount', 15, 2);
            $table->enum('status', ['unpaid', 'paid', 'overdue'])->default('unpaid');
            $table->boolean('is_proforma')->default(false);
            $table->timestamps();
        });

        Schema::create('temp_invoice_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('temp_invoice_id')->constrained('temp_invoices')->onDelete('cascade');
            $table->text('description');
            $table->string('type')->default('item');
            $table->integer('quantity')->default(1);
            $table->decimal('unit_price', 15, 2);
            $table->decimal('amount', 15, 2);
            $table->decimal('sscl_amount', 15, 2)->nullable();
            $table->decimal('vat_amount', 15, 2)->nullable();
            $table->decimal('total_with_vat', 15, 2);
            $table->string('department')->nullable();
            $table->string('revenue_category')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('temp_invoice_items');
        Schema::dropIfExists('temp_invoices');
    }
};
