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
        Schema::create('trades', function (Blueprint $table) {
            $table->id();
            $table->string('symbol');
            $table->string('type');
            $table->decimal('price', 20, 8);
            $table->decimal('close_price', 20, 8)->nullable();
            $table->enum('status', ['open', 'closed'])->default('open');
            $table->string('txid')->nullable();
            $table->timestamps();

            $table->index(['symbol', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('trades');
    }
};
