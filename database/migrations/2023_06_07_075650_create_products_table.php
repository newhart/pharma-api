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
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->unsignedInteger('quantityBoite')->nullable();
            $table->unsignedInteger('quantityPlaquette')->nullable();
            $table->unsignedInteger('quantityGellule')->nullable();
            $table->unsignedDouble('priceBoite')->default(0.00);
            $table->unsignedDouble('pricePlaquette')->default(0.00);
            $table->unsignedDouble('priceGellule')->default(0.00);
            $table->unsignedInteger('numberPlaquette')->default(0);
            $table->unsignedInteger('numberGellule')->default(0);
            $table->date('datePeremption');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
