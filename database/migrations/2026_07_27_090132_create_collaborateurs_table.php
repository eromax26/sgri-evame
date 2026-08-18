<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('collaborateurs', function (Blueprint $table) {
            $table->id();
            $table->string('matricule')->unique();
            $table->string('nom');
            $table->string('prenom');
            $table->string('identifiant')->unique();
            $table->string('email')->nullable()->unique();
            $table->string('password')->nullable();
            $table->string('telephone', 20)->nullable();
            $table->foreignId('departement_id')->constrained('departements')->onDelete('restrict');
            $table->string('direction')->nullable();
            $table->string('fonction')->nullable();
            $table->string('site')->nullable();
            $table->date('date_entree')->nullable();
            $table->date('date_sortie')->nullable();
            $table->string('mode_facturation', 30)->nullable();
            $table->string('photo')->nullable();
            $table->string('statut')->default('actif');
            $table->unsignedTinyInteger('tentatives_echouees')->default(0);
            $table->rememberToken();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('collaborateurs');
    }
};