<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Deux problemes sur le module Commandes :
 *
 *  1. La colonne commandes.date_achat, utilisee par le modele Commande et par
 *     LigneCommande::livrer(), n'avait jamais ete creee : la premiere reception
 *     echouait en erreur SQL.
 *
 *  2. Les tables commandes / ligne_commandes ont ete creees en MyISAM (moteur par
 *     defaut du serveur), qui ignore silencieusement les cles etrangeres : les
 *     ->constrained()->onDelete(...) de 2026_09_20_000001/000002 n'existaient pas
 *     reellement. Consequence : supprimer un brouillon laissait ses lignes derriere
 *     lui. On reprend donc la meme demarche que la migration de conversion du
 *     2026_09_06 : bascule en InnoDB puis pose des contraintes reelles.
 */
return new class extends Migration
{
    /** Colonnes etrangeres a garantir : [table, colonne, table referencee, regle de suppression] */
    private array $contraintes = [
        ['commandes', 'collaborateur_id', 'collaborateurs', 'restrict'],
        ['ligne_commandes', 'commande_id', 'commandes', 'cascade'],
        ['ligne_commandes', 'article_id', 'articles', 'restrict'],
        ['mouv_stocks', 'commande_id', 'commandes', 'set null'],
        ['mouv_stocks', 'ligne_commande_id', 'ligne_commandes', 'set null'],
    ];

    public function up(): void
    {
        if (!Schema::hasColumn('commandes', 'date_achat')) {
            Schema::table('commandes', function (Blueprint $table) {
                $table->date('date_achat')->nullable()->after('date_commande');
            });
        }

        // SQLite (tests) ne sait pas ajouter de contrainte sur une table existante.
        if (DB::getDriverName() !== 'mysql') {
            return;
        }

        foreach (['commandes', 'ligne_commandes'] as $table) {
            DB::statement("ALTER TABLE `{$table}` ENGINE = InnoDB");
        }

        foreach ($this->contraintes as [$table, $colonne, $reference, $regle]) {
            if ($this->contrainteExiste($table, $colonne)) {
                continue;
            }

            Schema::table($table, function (Blueprint $blueprint) use ($colonne, $reference, $regle) {
                $blueprint->foreign($colonne)->references('id')->on($reference)->onDelete($regle);
            });
        }
    }

    public function down(): void
    {
        if (DB::getDriverName() === 'mysql') {
            foreach (array_reverse($this->contraintes) as [$table, $colonne]) {
                if ($this->contrainteExiste($table, $colonne)) {
                    Schema::table($table, function (Blueprint $blueprint) use ($colonne) {
                        $blueprint->dropForeign([$colonne]);
                    });
                }
            }
        }

        Schema::table('commandes', function (Blueprint $table) {
            $table->dropColumn('date_achat');
        });
    }

    private function contrainteExiste(string $table, string $colonne): bool
    {
        return DB::table('information_schema.KEY_COLUMN_USAGE')
            ->whereRaw('CONSTRAINT_SCHEMA = DATABASE()')
            ->where('TABLE_NAME', $table)
            ->where('COLUMN_NAME', $colonne)
            ->whereNotNull('REFERENCED_TABLE_NAME')
            ->exists();
    }
};

