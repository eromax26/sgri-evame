<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Un jour de menu peut exister sans plat choisi (brouillon en cours de
     * remplissage) : MenuController::store() et publier() le supposent deja,
     * mais la colonne etait restee NOT NULL depuis la migration d'origine.
     */
    public function up(): void
    {
        DB::statement('ALTER TABLE ligne_menus MODIFY plat_id BIGINT UNSIGNED NULL');
    }

    public function down(): void
    {
        DB::statement('ALTER TABLE ligne_menus MODIFY plat_id BIGINT UNSIGNED NOT NULL');
    }
};
