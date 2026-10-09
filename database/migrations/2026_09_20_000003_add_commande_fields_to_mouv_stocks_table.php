<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('mouv_stocks', function (Blueprint $table) {
            $table->unsignedBigInteger('commande_id')->nullable()->after('motif_sortie');
            $table->unsignedBigInteger('ligne_commande_id')->nullable()->after('commande_id');
            
            $table->index('commande_id');
            $table->index('ligne_commande_id');
        });
    }

    public function down(): void
    {
        Schema::table('mouv_stocks', function (Blueprint $table) {
            $table->dropIndex(['commande_id']);
            $table->dropIndex(['ligne_commande_id']);
            $table->dropColumn(['commande_id', 'ligne_commande_id']);
        });
    }
};