<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('settings', function (Blueprint $blueprint) {
            $blueprint->id();
            $blueprint->string('key')->unique();
            $blueprint->text('value')->nullable();
            $blueprint->string('group')->default('general');
            $blueprint->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('settings');
    }
};
