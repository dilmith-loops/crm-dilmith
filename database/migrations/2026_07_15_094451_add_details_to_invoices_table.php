<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->string('brand_name')->nullable()->after('customer_id');
            $table->string('attention_to')->nullable()->after('brand_name');
            $table->string('address_line_1')->nullable()->after('attention_to');
            $table->string('address_line_2')->nullable()->after('address_line_1');
            $table->string('address_line_3')->nullable()->after('address_line_2');
            $table->string('designation')->nullable()->after('address_line_3');
            $table->string('currency')->default('LKR')->after('designation');
            $table->string('heading')->nullable()->after('currency');
            $table->text('terms')->nullable()->after('heading');
            $table->text('special_terms')->nullable()->after('terms');
            $table->string('advance_payment')->nullable()->after('special_terms');
            $table->decimal('advance_percentage', 10, 2)->default(0)->nullable()->after('advance_payment');
            $table->decimal('advance_received_amount', 15, 2)->default(0)->nullable()->after('advance_percentage');
            $table->string('invoice_type')->default('tax_invoice')->after('advance_received_amount');
            $table->string('senior_manager')->nullable()->after('invoice_type');
            $table->boolean('sscl_applicable')->default(false)->after('senior_manager');
            $table->boolean('vat_applicable')->default(false)->after('sscl_applicable');
            $table->decimal('proforma_percentage', 10, 2)->default(50)->nullable()->after('vat_applicable');
            $table->string('proforma_tax')->default('with_tax')->nullable()->after('proforma_percentage');
            $table->boolean('proforma_with_tax')->default(true)->nullable()->after('proforma_tax');
        });

        // Copy data from quotations to invoices for existing invoices
        DB::table('invoices')
            ->join('quotations', 'invoices.quotation_id', '=', 'quotations.id')
            ->update([
                'invoices.brand_name' => DB::raw('quotations.brand_name'),
                'invoices.attention_to' => DB::raw('quotations.attention_to'),
                'invoices.address_line_1' => DB::raw('quotations.address_line_1'),
                'invoices.address_line_2' => DB::raw('quotations.address_line_2'),
                'invoices.address_line_3' => DB::raw('quotations.address_line_3'),
                'invoices.designation' => DB::raw('quotations.designation'),
                'invoices.currency' => DB::raw('quotations.currency'),
                'invoices.heading' => DB::raw('quotations.heading'),
                'invoices.terms' => DB::raw('quotations.terms'),
                'invoices.special_terms' => DB::raw('quotations.special_terms'),
                'invoices.advance_payment' => DB::raw('quotations.advance_payment'),
                'invoices.advance_percentage' => DB::raw('quotations.advance_percentage'),
                'invoices.advance_received_amount' => DB::raw('quotations.advance_received_amount'),
                'invoices.invoice_type' => DB::raw('quotations.invoice_type'),
                'invoices.senior_manager' => DB::raw('quotations.senior_manager'),
                'invoices.sscl_applicable' => DB::raw('quotations.sscl_applicable'),
                'invoices.vat_applicable' => DB::raw('quotations.vat_applicable'),
                'invoices.proforma_percentage' => DB::raw('quotations.proforma_percentage'),
                'invoices.proforma_tax' => DB::raw('quotations.proforma_tax'),
                'invoices.proforma_with_tax' => DB::raw('quotations.proforma_with_tax'),
            ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->dropColumn([
                'brand_name',
                'attention_to',
                'address_line_1',
                'address_line_2',
                'address_line_3',
                'designation',
                'currency',
                'heading',
                'terms',
                'special_terms',
                'advance_payment',
                'advance_percentage',
                'advance_received_amount',
                'invoice_type',
                'senior_manager',
                'sscl_applicable',
                'vat_applicable',
                'proforma_percentage',
                'proforma_tax',
                'proforma_with_tax',
            ]);
        });
    }
};
