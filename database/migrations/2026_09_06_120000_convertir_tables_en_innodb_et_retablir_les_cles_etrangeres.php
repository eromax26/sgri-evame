<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Les tables avaient ete creees en MyISAM, moteur qui ignore silencieusement les
 * cles etrangeres : tous les ->constrained()->onDelete(...) des migrations
 * precedentes n'existaient donc pas reellement en base. Resultat : supprimer un
 * menu laissait ses lignes (et les reservations associees) orphelines.
 *
 * Cette migration bascule les tables en InnoDB puis reinstalle les contraintes
 * telles qu'elles etaient declarees a l'origine.
 */
return new class extends Migration
{
    /**
     * Tables a convertir, dans un ordre sans importance (la conversion seule
     * ne cree aucune contrainte).
     */
    private array $tables = [
        'societes',
        'departements',
        'roles',
        'collaborateurs',
        'acces',
        'plats',
        'menus',
        'ligne_menus',
        'selections_repas',
        'articles',
        'mouv_stocks',
    ];

    public function up(): void
    {
        if (DB::getDriverName() !== 'mysql') {
            return;
        }

        foreach ($this->tables as $table) {
            if (Schema::hasTable($table)) {
                DB::statement("ALTER TABLE `{$table}` ENGINE = InnoDB");
            }
        }

        Schema::table('collaborateurs', function ($table) {
            $table->foreign('departement_id')->references('id')->on('departements')->onDelete('restrict');
        });

        Schema::table('acces', function ($table) {
            $table->foreign('collaborateur_id')->references('id')->on('collaborateurs')->onDelete('cascade');
            $table->foreign('role_id')->references('id')->on('roles')->onDelete('cascade');
        });

        Schema::table('ligne_menus', function ($table) {
            $table->foreign('menu_id')->references('id')->on('menus')->onDelete('cascade');
            $table->foreign('plat_id')->references('id')->on('plats')->onDelete('restrict');
        });

        Schema::table('selections_repas', function ($table) {
            $table->foreign('ligne_menu_id')->references('id')->on('ligne_menus')->onDelete('cascade');
            $table->foreign('collaborateur_id')->references('id')->on('collaborateurs')->onDelete('cascade');
            $table->foreign('agent_securite_id')->references('id')->on('collaborateurs')->onDelete('set null');
        });

        Schema::table('mouv_stocks', function ($table) {
            $table->foreign('article_id')->references('id')->on('articles')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        if (DB::getDriverName() !== 'mysql') {
            return;
        }

        Schema::table('mouv_stocks', function ($table) {
            $table->dropForeign(['article_id']);
        });

        Schema::table('selections_repas', function ($table) {
            $table->dropForeign(['ligne_menu_id']);
            $table->dropForeign(['collaborateur_id']);
            $table->dropForeign(['agent_securite_id']);
        });

        Schema::table('ligne_menus', function ($table) {
            $table->dropForeign(['menu_id']);
            $table->dropForeign(['plat_id']);
        });

        Schema::table('acces', function ($table) {
            $table->dropForeign(['collaborateur_id']);
            $table->dropForeign(['role_id']);
        });

        Schema::table('collaborateurs', function ($table) {
            $table->dropForeign(['departement_id']);
        });
    }
};
