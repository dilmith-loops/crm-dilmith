<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('quotations', function (Blueprint $table) {
            if (!Schema::hasColumn('quotations', 'proforma_percentage')) {
                $table->decimal('proforma_percentage', 5, 2)->nullable()->after('third_party_cost');
            }
            if (!Schema::hasColumn('quotations', 'proforma_tax')) {
                $table->string('proforma_tax')->nullable()->after('proforma_percentage');
            }
        });
    }

    public function down(): void
    {
        Schema::table('quotations', function (Blueprint $table) {
            $table->dropColumn(['proforma_percentage', 'proforma_tax']);
        });
    }
};
