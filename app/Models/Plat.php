<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Plat extends Model
{
    use HasFactory;

    protected $fillable = [
        'code_plat',
        'libelle',
        'description',
        'categorie',
        'prix',
        'photo',
        'statut',
    ];

    protected function casts(): array
    {
        return [
            'prix' => 'decimal:2',
        ];
    }

    public function estActif(): bool
    {
        return $this->statut === 'actif';
    }
}