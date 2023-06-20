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
            $table->unsignedDouble('quantityBoite')->nullable()->change();
            $table->unsignedDouble('quantityPlaquette')->nullable()->change();
            $table->unsignedDouble('quantityGellule')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->unsignedInteger('quantityBoite')->nullable();
            $table->unsignedInteger('quantityPlaquette')->nullable();
            $table->unsignedInteger('quantityGellule')->nullable();
        });
    }
};
