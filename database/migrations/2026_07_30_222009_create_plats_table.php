<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('plats', function (Blueprint $table) {
            $table->id();
            $table->string('code_plat', 20)->unique();
            $table->string('libelle');
            $table->text('description')->nullable();
            $table->string('categorie')->nullable();
            $table->decimal('prix', 10, 2)->nullable();
            $table->string('photo')->nullable();
            $table->string('statut')->default('actif');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('plats');
    }
};