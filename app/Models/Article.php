<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Article extends Model
{
    use HasFactory;

    protected $fillable = [
        'libelle',
        'unite_mesure',
        'quantite_stock',
        'seuil_minimum',
    ];

    protected function casts(): array
    {
        return [
            'quantite_stock' => 'decimal:2',
            'seuil_minimum' => 'decimal:2',
        ];
    }

    public function mouvements(): HasMany
    {
        return $this->hasMany(MouvStock::class);
    }

    public function seuilAtteint(): bool
    {
        return $this->quantite_stock <= $this->seuil_minimum;
    }
}