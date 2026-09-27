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
        Schema::create('menu_gacha_flags', function (Blueprint $table) {
            $table->id();
            $table->foreignId('menu_gacha_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->unique(['menu_gacha_id', 'name']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('menu_gacha_flags');
    }
};
