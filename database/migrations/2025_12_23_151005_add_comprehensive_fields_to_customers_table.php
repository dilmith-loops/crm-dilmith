<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            // Company Information
            $table->string('billing_address')->nullable()->after('address');

            // General Office Contact
            $table->string('telephone')->nullable()->after('phone');
            $table->string('fax')->nullable()->after('telephone');

            // Business Registration
            $table->string('business_registration_number')->nullable()->after('fax');

            // Primary Point of Contact
            $table->string('primary_contact_name')->nullable()->after('business_registration_number');
            $table->string('primary_contact_designation')->nullable()->after('primary_contact_name');
            $table->string('primary_contact_mobile')->nullable()->after('primary_contact_designation');
            $table->string('primary_contact_office')->nullable()->after('primary_contact_mobile');
            $table->string('primary_contact_email')->nullable()->after('primary_contact_office');

            // Promo Point of Contact
            $table->string('promo_contact_name')->nullable()->after('primary_contact_email');
            $table->string('promo_contact_designation')->nullable()->after('promo_contact_name');
            $table->string('promo_contact_mobile')->nullable()->after('promo_contact_designation');
            $table->string('promo_contact_office')->nullable()->after('promo_contact_mobile');
            $table->string('promo_contact_email')->nullable()->after('promo_contact_office');

            // Tax Information
            $table->string('customer_tax_number')->nullable()->after('promo_contact_email');
            $table->string('customer_vat_registration_number')->nullable()->after('customer_tax_number');
            $table->string('customer_suspended_vat_registration_number')->nullable()->after('customer_vat_registration_number');

            // Credit Terms
            $table->string('approved_credit_period')->nullable()->after('customer_suspended_vat_registration_number');
            $table->decimal('approved_credit_limit', 15, 2)->nullable()->after('approved_credit_period');
        });
    }

    public function down(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->dropColumn([
                'billing_address',
                'telephone',
                'fax',
                'business_registration_number',
                'primary_contact_name',
                'primary_contact_designation',
                'primary_contact_mobile',
                'primary_contact_office',
                'primary_contact_email',
                'promo_contact_name',
                'promo_contact_designation',
                'promo_contact_mobile',
                'promo_contact_office',
                'promo_contact_email',
                'customer_tax_number',
                'customer_vat_registration_number',
                'customer_suspended_vat_registration_number',
                'approved_credit_period',
                'approved_credit_limit',
            ]);
        });
    }
};
