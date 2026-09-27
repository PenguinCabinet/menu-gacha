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
        Schema::create('menu_gacha_flag_menu_gacha_item', function (Blueprint $table) {
            $table->foreignId('menu_gacha_flag_id')->constrained()->cascadeOnDelete();
            $table->foreignId('menu_gacha_item_id')->constrained()->cascadeOnDelete();
            $table->primary(['menu_gacha_flag_id', 'menu_gacha_item_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('menu_gacha_flag_menu_gacha_item');
    }
};
