<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Rien n'empechait de creer plusieurs brouillons pour la meme semaine :
     * MenuController::store() ne verifiait pas l'existant, ce qui donnait
     * l'impression que le brouillon "se reinitialisait" a chaque nouvelle
     * creation. La verification cote controleur est desormais faite, cette
     * contrainte est le filet de securite cote base.
     */
    public function up(): void
    {
        Schema::table('menus', function (Blueprint $table) {
            $table->unique('date_debut_semaine');
        });
    }

    public function down(): void
    {
        Schema::table('menus', function (Blueprint $table) {
            $table->dropUnique(['date_debut_semaine']);
        });
    }
};
