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
        Schema::table('customers', function (Blueprint $table) {
            $table->renameColumn('promo_contact_name', 'finance_contact_name');
            $table->renameColumn('promo_contact_designation', 'finance_contact_designation');
            $table->renameColumn('promo_contact_mobile', 'finance_contact_mobile');
            $table->renameColumn('promo_contact_office', 'finance_contact_office');
            $table->renameColumn('promo_contact_email', 'finance_contact_email');
        });
    }

    public function down(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->renameColumn('finance_contact_name', 'promo_contact_name');
            $table->renameColumn('finance_contact_designation', 'promo_contact_designation');
            $table->renameColumn('finance_contact_mobile', 'promo_contact_mobile');
            $table->renameColumn('finance_contact_office', 'promo_contact_office');
            $table->renameColumn('finance_contact_email', 'promo_contact_email');
        });
    }
};
