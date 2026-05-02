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
        Schema::create('signal_histories', function (Blueprint $table) {
            $table->id();
            $table->string('symbol');
            $table->enum('type', ['BULLISH OB', 'BEARISH OB']);
            $table->decimal('current_price', 10, 8);
            $table->decimal('zone_bottom', 10, 8);
            $table->decimal('zone_top', 10, 8);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('trade_histories');
    }
};
