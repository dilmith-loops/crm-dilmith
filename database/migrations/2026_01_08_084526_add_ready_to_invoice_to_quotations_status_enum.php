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
        DB::statement("ALTER TABLE quotations MODIFY COLUMN status ENUM('draft', 'sent', 'approved', 'accepted', 'rejected', 'invoiced', 'ready_to_invoice') DEFAULT 'draft'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement("ALTER TABLE quotations MODIFY COLUMN status ENUM('draft', 'sent', 'approved', 'accepted', 'rejected', 'invoiced') DEFAULT 'draft'");
    }
};
