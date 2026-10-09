<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Un jour de menu peut exister sans plat choisi (brouillon en cours de
     * remplissage) : MenuController::store() et publier() le supposent deja,
     * mais la colonne etait restee NOT NULL depuis la migration d'origine.
     *
     * MySQL modifie la colonne en place (MODIFY) ; SQLite, utilise par la suite
     * de tests, ne connait pas MODIFY et exige une reconstruction de table, ce
     * que fait Schema::...->change().
     */
    public function up(): void
    {
        $this->rendrePlatIdNullable(true);
    }

    public function down(): void
    {
        $this->rendrePlatIdNullable(false);
    }

    private function rendrePlatIdNullable(bool $nullable): void
    {
        if (DB::getDriverName() === 'mysql') {
            DB::statement('ALTER TABLE ligne_menus MODIFY plat_id BIGINT UNSIGNED ' . ($nullable ? 'NULL' : 'NOT NULL'));

            return;
        }

        Schema::table('ligne_menus', function (Blueprint $table) use ($nullable) {
            $table->foreignId('plat_id')->nullable($nullable)->change();
        });
    }
};

