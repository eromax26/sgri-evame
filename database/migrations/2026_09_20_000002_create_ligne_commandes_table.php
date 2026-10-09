<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ligne_commandes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('commande_id')->constrained('commandes')->onDelete('cascade');
            $table->foreignId('article_id')->constrained('articles')->onDelete('restrict');
            $table->decimal('quantite_demandee', 10, 2);
            $table->decimal('prix_estime_unitaire', 10, 2)->nullable();
            $table->decimal('quantite_livree', 10, 2)->default(0);
            $table->decimal('prix_achat_reel', 10, 2)->nullable();
            $table->text('commentaire')->nullable();
            $table->timestamps();

            $table->unique(['commande_id', 'article_id']);
            $table->index('article_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ligne_commandes');
    }
};