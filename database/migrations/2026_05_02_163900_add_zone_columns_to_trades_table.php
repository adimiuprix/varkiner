<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('trades', function (Blueprint $table) {
            $table->decimal('zone_bottom', 10, 8)->nullable()->after('price');
            $table->decimal('zone_top', 10, 8)->nullable()->after('zone_bottom');
        });
    }

    public function down(): void
    {
        Schema::table('trades', function (Blueprint $table) {
            $table->dropColumn(['zone_bottom', 'zone_top']);
        });
    }
};
