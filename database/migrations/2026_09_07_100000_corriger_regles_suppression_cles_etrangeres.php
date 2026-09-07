<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * La migration de bascule vers InnoDB a retabli les cles etrangeres, mais quatre
 * d'entre elles ne correspondent pas a ce que les migrations d'origine declaraient :
 *
 *  - departements.societe_id        : contrainte absente
 *  - mouv_stocks.collaborateur_id   : contrainte absente
 *  - mouv_stocks.article_id         : posee en CASCADE au lieu de RESTRICT
 *  - acces.role_id                  : posee en CASCADE au lieu de RESTRICT
 *
 * Les deux CASCADE sont les plus genantes : supprimer un article effacerait tout
 * son historique de mouvements de stock, alors que la regle voulue est de refuser
 * la suppression tant que cet historique existe.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (DB::getDriverName() !== 'mysql') {
            return;
        }

        // Contraintes qui n'avaient pas ete retablies.
        Schema::table('departements', function ($table) {
            $table->foreign('societe_id')->references('id')->on('societes')->onDelete('restrict');
        });

        Schema::table('mouv_stocks', function ($table) {
            $table->foreign('collaborateur_id')->references('id')->on('collaborateurs')->onDelete('restrict');
        });

        // Contraintes a repositionner en RESTRICT : MySQL ne sait pas modifier une
        // cle etrangere en place, il faut la supprimer puis la recreer.
        Schema::table('mouv_stocks', function ($table) {
            $table->dropForeign(['article_id']);
            $table->foreign('article_id')->references('id')->on('articles')->onDelete('restrict');
        });

        Schema::table('acces', function ($table) {
            $table->dropForeign(['role_id']);
            $table->foreign('role_id')->references('id')->on('roles')->onDelete('restrict');
        });
    }

    public function down(): void
    {
        if (DB::getDriverName() !== 'mysql') {
            return;
        }

        Schema::table('acces', function ($table) {
            $table->dropForeign(['role_id']);
            $table->foreign('role_id')->references('id')->on('roles')->onDelete('cascade');
        });

        Schema::table('mouv_stocks', function ($table) {
            $table->dropForeign(['article_id']);
            $table->foreign('article_id')->references('id')->on('articles')->onDelete('cascade');
        });

        Schema::table('mouv_stocks', function ($table) {
            $table->dropForeign(['collaborateur_id']);
        });

        Schema::table('departements', function ($table) {
            $table->dropForeign(['societe_id']);
        });
    }
};
