<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('standard_terms', function (Blueprint $blueprint) {
            $blueprint->id();
            $blueprint->text('content');
            $blueprint->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('standard_terms');
    }
};
