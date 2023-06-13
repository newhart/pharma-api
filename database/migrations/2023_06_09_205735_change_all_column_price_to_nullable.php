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
        Schema::table('products', function (Blueprint $table) {
            $table->unsignedDouble('priceBoite')->nullable()->change();
            $table->unsignedDouble('pricePlaquette')->nullable()->change();
            $table->unsignedDouble('priceGellule')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->unsignedDouble('priceBoite')->default(0.00);
            $table->unsignedDouble('pricePlaquette')->default(0.00);
            $table->unsignedDouble('priceGellule')->default(0.00);
        });
    }
};
