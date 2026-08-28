<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('selections_repas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ligne_menu_id')->constrained('ligne_menus')->onDelete('cascade');
            $table->foreignId('collaborateur_id')->constrained('collaborateurs')->onDelete('cascade');
            $table->foreignId('agent_securite_id')->nullable()->constrained('collaborateurs')->onDelete('set null');
            $table->dateTime('date_selection')->nullable();
            $table->dateTime('date_impression')->nullable();
            $table->dateTime('date_retrait')->nullable();
            $table->decimal('prix', 10, 2)->nullable();
            $table->string('statut')->default('demande');
            $table->string('numero_ticket')->nullable()->unique();
            $table->string('periode_facturation', 7)->nullable();
            $table->string('statut_facturation')->default('non_facture');
            $table->timestamps();

            $table->unique(['ligne_menu_id', 'collaborateur_id']);
        });

        // Reprise des reservations existantes (une seule par ligne aujourd'hui) avant de retirer les colonnes de ligne_menus.
        DB::table('ligne_menus')
            ->whereNotNull('collaborateur_id')
            ->orderBy('id')
            ->get()
            ->each(function ($ligne) {
                DB::table('selections_repas')->insert([
                    'ligne_menu_id' => $ligne->id,
                    'collaborateur_id' => $ligne->collaborateur_id,
                    'agent_securite_id' => $ligne->agent_securite_id,
                    'date_selection' => $ligne->date_selection,
                    'date_impression' => $ligne->date_impression,
                    'date_retrait' => $ligne->date_retrait,
                    'prix' => $ligne->prix,
                    'statut' => $ligne->statut === 'prevu' ? 'demande' : $ligne->statut,
                    'numero_ticket' => $ligne->numero_ticket,
                    'periode_facturation' => $ligne->periode_facturation,
                    'statut_facturation' => $ligne->statut_facturation,
                    'created_at' => $ligne->created_at,
                    'updated_at' => $ligne->updated_at,
                ]);
            });

        Schema::table('ligne_menus', function (Blueprint $table) {
            $table->dropForeign(['collaborateur_id']);
            $table->dropForeign(['agent_securite_id']);
            $table->dropColumn([
                'collaborateur_id',
                'agent_securite_id',
                'date_selection',
                'date_impression',
                'date_retrait',
                'prix',
                'statut',
                'numero_ticket',
                'periode_facturation',
                'statut_facturation',
            ]);
        });
    }

    public function down(): void
    {
        Schema::table('ligne_menus', function (Blueprint $table) {
            $table->foreignId('collaborateur_id')->nullable()->constrained('collaborateurs')->onDelete('set null');
            $table->foreignId('agent_securite_id')->nullable()->constrained('collaborateurs')->onDelete('set null');
            $table->dateTime('date_selection')->nullable();
            $table->dateTime('date_impression')->nullable();
            $table->dateTime('date_retrait')->nullable();
            $table->decimal('prix', 10, 2)->nullable();
            $table->string('statut')->default('prevu');
            $table->string('numero_ticket')->nullable()->unique();
            $table->string('periode_facturation', 7)->nullable();
            $table->string('statut_facturation')->default('non_facture');
        });

        DB::table('selections_repas')
            ->orderBy('id')
            ->get()
            ->each(function ($selection) {
                DB::table('ligne_menus')->where('id', $selection->ligne_menu_id)->update([
                    'collaborateur_id' => $selection->collaborateur_id,
                    'agent_securite_id' => $selection->agent_securite_id,
                    'date_selection' => $selection->date_selection,
                    'date_impression' => $selection->date_impression,
                    'date_retrait' => $selection->date_retrait,
                    'prix' => $selection->prix,
                    'statut' => $selection->statut,
                    'numero_ticket' => $selection->numero_ticket,
                    'periode_facturation' => $selection->periode_facturation,
                    'statut_facturation' => $selection->statut_facturation,
                ]);
            });

        Schema::dropIfExists('selections_repas');
    }
};
