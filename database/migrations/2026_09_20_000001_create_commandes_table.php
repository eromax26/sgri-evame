<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('commandes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('collaborateur_id')->constrained('collaborateurs')->onDelete('restrict');
            $table->date('date_commande');
            $table->enum('statut', ['brouillon', 'validee', 'livree_partielle', 'livree', 'annulee'])->default('brouillon');
            $table->decimal('prix_estime_total', 12, 2)->nullable();
            $table->text('commentaire')->nullable();
            $table->timestamps();

            $table->index(['statut', 'date_commande']);
            $table->index('collaborateur_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('commandes');
    }
};