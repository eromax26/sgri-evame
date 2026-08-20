<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Table "users" laissee par le squelette Laravel par defaut : l'appli
     * authentifie exclusivement via "collaborateurs" (config/auth.php),
     * aucun code ne reference App\Models\User. Jamais utilisee (0 ligne).
     */
    public function up(): void
    {
        Schema::dropIfExists('users');
    }

    public function down(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->rememberToken();
            $table->timestamps();
        });
    }
};
