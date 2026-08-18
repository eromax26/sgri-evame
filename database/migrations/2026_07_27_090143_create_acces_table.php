<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('acces', function (Blueprint $table) {
            $table->id();
            $table->foreignId('collaborateur_id')->constrained('collaborateurs')->onDelete('cascade');
            $table->foreignId('role_id')->constrained('roles')->onDelete('restrict');
            $table->date('date_attribution')->nullable();
            $table->timestamps();

            $table->unique(['collaborateur_id', 'role_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('acces');
    }
};