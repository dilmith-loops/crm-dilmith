<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('system_currencies', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique(); // USD, LKR, etc.
            $table->string('name')->nullable(); // US Dollar, Sri Lankan Rupee
            $table->string('symbol')->nullable(); // $, Rs
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('system_currencies');
    }
};
