<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ligne_menus', function (Blueprint $table) {
            $table->id();
            $table->foreignId('menu_id')->constrained('menus')->onDelete('cascade');
            $table->foreignId('plat_id')->constrained('plats')->onDelete('restrict');
            $table->foreignId('collaborateur_id')->nullable()->constrained('collaborateurs')->onDelete('set null');
            $table->foreignId('agent_securite_id')->nullable()->constrained('collaborateurs')->onDelete('set null');
            $table->date('date_repas');
            $table->dateTime('date_selection')->nullable();
            $table->dateTime('date_impression')->nullable();
            $table->dateTime('date_retrait')->nullable();
            $table->decimal('prix', 10, 2)->nullable();
            $table->string('statut')->default('prevu');
            $table->string('numero_ticket')->nullable()->unique();
            $table->string('periode_facturation', 7)->nullable();
            $table->string('statut_facturation')->default('non_facture');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ligne_menus');
    }
};