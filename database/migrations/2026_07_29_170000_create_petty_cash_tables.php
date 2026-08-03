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
        Schema::create('petty_cash_requests', function (Blueprint $table) {
            $table->id();
            $table->string('reference_number')->unique();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('hod_id')->nullable()->constrained('users')->onDelete('set null');
            $table->string('department')->nullable();
            $table->string('job_number')->nullable();
            $table->decimal('total_amount', 15, 2)->default(0);
            $table->string('status')->default('pending_hod'); // pending_hod, rejected_by_hod, pending_super_admin, rejected_by_super_admin, approved
            $table->text('hod_rejection_note')->nullable();
            $table->text('admin_rejection_note')->nullable();
            $table->integer('reappeal_count')->default(0);
            $table->timestamps();
        });

        Schema::create('petty_cash_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('petty_cash_request_id')->constrained('petty_cash_requests')->onDelete('cascade');
            $table->foreignId('expense_category_id')->nullable()->constrained('expense_categories')->onDelete('set null');
            $table->text('description')->nullable();
            $table->decimal('amount', 15, 2)->default(0);
            $table->timestamps();
        });

        Schema::create('petty_cash_proofs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('petty_cash_request_id')->constrained('petty_cash_requests')->onDelete('cascade');
            $table->string('file_path');
            $table->string('file_name');
            $table->string('file_type')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('petty_cash_proofs');
        Schema::dropIfExists('petty_cash_items');
        Schema::dropIfExists('petty_cash_requests');
    }
};
