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
        Schema::table('settings', function (Blueprint $table) {
            // Ajout de la colonne 'logo_id' à la table 'settings'
            $table->unsignedBigInteger('logo_id')->nullable()->after('id');
            // Définition de la contrainte de clé étrangère
            $table->foreign('logo_id')->references('id')->on('logos')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('settings', function (Blueprint $table) {
            // Suppression de la contrainte de clé étrangère et de la colonne 'logo_id'
            $table->dropForeign(['logo_id']);
            $table->dropColumn('logo_id');
        });
    }
};
