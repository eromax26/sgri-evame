<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mouv_stocks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('article_id')->constrained('articles')->onDelete('restrict');
            $table->foreignId('collaborateur_id')->constrained('collaborateurs')->onDelete('restrict');
            $table->string('type_mouvement', 10);
            $table->decimal('quantite', 10, 2);
            $table->date('date_mouvement');
            $table->decimal('prix_achat', 10, 2)->nullable();
            $table->string('motif_sortie')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mouv_stocks');
    }
};