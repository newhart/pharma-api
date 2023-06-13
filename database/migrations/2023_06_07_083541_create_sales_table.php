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
        Schema::create('sales', function (Blueprint $table) {
            $table->id();
            $table->date('saleDate');
            $table->unsignedDouble('saleAmout');
            $table->unsignedDouble('salePayed');
            $table->unsignedDouble('saleStay');
            $table->string('estACredit');
            $table->string('playmentMode');
            $table->date('playmentDatePrevueAt');
            $table->string('clientName')->nullable();
            $table->longText('description')->nullable();
            $table->string('stateSale')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sales');
    }
};
