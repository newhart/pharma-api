<?php

use App\Models\Product;
use App\Models\Sale;
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
        Schema::create('product_sale', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(Product::class)->constrained();
            $table->foreignIdFor(Sale::class)->constrained();
            $table->unsignedDouble('quantityGellule');
            $table->unsignedDouble('quantityPlaquette');
            $table->unsignedDouble('quantityBoite');
            $table->unsignedDouble('priceSaleGellule')->nullable();
            $table->unsignedDouble('priceSalePlaquette')->nullable();
            $table->unsignedDouble('priceSaleBoite')->nullable();
            $table->unsignedDouble('amount');
            $table->unsignedDouble('remise')->nullable();
            $table->string('user')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_sale');
    }
};
