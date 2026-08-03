<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // For MySQL, we can use a raw statement to alter the enum
        DB::statement("ALTER TABLE quotations MODIFY COLUMN status ENUM('draft', 'sent', 'approved', 'accepted', 'rejected', 'invoiced') DEFAULT 'draft'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement("ALTER TABLE quotations MODIFY COLUMN status ENUM('draft', 'sent', 'accepted', 'rejected', 'invoiced') DEFAULT 'draft'");
    }
};
