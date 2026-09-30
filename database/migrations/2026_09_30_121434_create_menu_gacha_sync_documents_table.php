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
        Schema::create('menu_gacha_sync_documents', function (Blueprint $table) {
            $table->foreignId('menu_gacha_id')->primary()->constrained()->cascadeOnDelete();
            $table->longText('state');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('menu_gacha_sync_documents');
    }
};
