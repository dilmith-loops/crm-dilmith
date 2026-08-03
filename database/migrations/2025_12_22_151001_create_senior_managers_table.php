<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('senior_managers', function (Blueprint $blueprint) {
            $blueprint->id();
            $blueprint->string('name');
            $blueprint->string('designation')->default('Senior Manager');
            $blueprint->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('senior_managers');
    }
};
