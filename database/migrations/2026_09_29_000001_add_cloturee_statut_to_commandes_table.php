<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * RG16 : une liste de commande partiellement livrée peut être clôturée, le reste
 * des achats n'étant plus attendu.
 *
 * statut est un ENUM : la valeur est ajoutée en fin de liste, ce qui évite à MySQL
 * de reconstruire la table. Les tests tournant sur SQLite (où un enum est un CHECK),
 * la colonne y est régénérée via le schema builder.
 */
return new class extends Migration
{
    private const STATUTS = ['brouillon', 'validee', 'livree_partielle', 'livree', 'annulee'];

    public function up(): void
    {
        $this->remplacerStatut([...self::STATUTS, 'cloturee']);
    }

    public function down(): void
    {
        // Une commande clôturée reste une commande terminée : on la bascule sur 'livree'
        // avant de retirer la valeur, sinon la modification de colonne échoue.
        DB::table('commandes')->where('statut', 'cloturee')->update(['statut' => 'livree']);

        $this->remplacerStatut(self::STATUTS);
    }

    private function remplacerStatut(array $statuts): void
    {
        if (DB::getDriverName() === 'mysql') {
            $valeurs = implode(',', array_map(fn ($statut) => "'{$statut}'", $statuts));

            DB::statement("ALTER TABLE `commandes` MODIFY `statut` ENUM({$valeurs}) NOT NULL DEFAULT 'brouillon'");

            return;
        }

        Schema::table('commandes', function (Blueprint $table) use ($statuts) {
            $table->enum('statut', $statuts)->default('brouillon')->nullable(false)->change();
        });
    }
};
