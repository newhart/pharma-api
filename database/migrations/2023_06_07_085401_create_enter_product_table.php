<?php

use App\Models\Enter;
use App\Models\Product;
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
        Schema::create('enter_product', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(Enter::class)->constrained();
            $table->foreignIdFor(Product::class)->constrained();

            $table->unsignedInteger('quantityEnter')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('enter_product');
    }
};
