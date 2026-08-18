<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Notifications\Notifiable;

class Collaborateur extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'matricule',
        'nom',
        'prenom',
        'identifiant',
        'email',
        'password',
        'telephone',
        'departement_id',
        'direction',
        'fonction',
        'site',
        'date_entree',
        'date_sortie',
        'mode_facturation',
        'photo',
        'statut',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'date_entree' => 'date',
            'date_sortie' => 'date',
            'password' => 'hashed',
        ];
    }

    // Laravel utilisera 'identifiant' comme login au lieu de 'email'
    public function username(): string
    {
        return 'identifiant';
    }

    public function departement(): BelongsTo
    {
        return $this->belongsTo(Departement::class);
    }

    public function acces(): HasMany
    {
        return $this->hasMany(Acces::class);
    }

    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class, 'acces');
    }

    public function estActif(): bool
    {
        return $this->statut === 'actif';
    }

    public function aLeRole(string $libelle): bool
    {
        return $this->roles()->where('libelle', $libelle)->exists();
    }
}