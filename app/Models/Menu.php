<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Menu extends Model
{
    use HasFactory;

    protected $fillable = [
        'date_debut_semaine',
        'date_fin_semaine',
        'statut_publication',
    ];

    protected function casts(): array
    {
        return [
            'date_debut_semaine' => 'date',
            'date_fin_semaine' => 'date',
        ];
    }

    public function lignesMenu(): HasMany
    {
        return $this->hasMany(LigneMenu::class);
    }

    public function estPublie(): bool
    {
        return $this->statut_publication === 'publie';
    }
}