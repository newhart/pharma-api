<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('settings', function (Blueprint $table) {
            $table->string('nomEntreprise')->nullable();
            $table->string('nif')->nullable();
            $table->string('stat')->nullable();
            $table->string('mail')->nullable();
            $table->string('tel')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('settings', function (Blueprint $table) {
            $table->dropColumn(['nomEntreprise', 'nif', 'stat', 'mail', 'tel']);
        });
    }
};
